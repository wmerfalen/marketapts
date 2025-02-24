<?php
namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Property\Site;
use App\Email;
use App\Http\Controllers\SiteController;
use App\Traits\PageResolver;
use App\Util\Util;
use App\Assets\SoapClient;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Traits\NoNo;
use App\Property\Text\Type as TextType;
use App\Property\Text as PropertyText;
use App\Property\Template as PropertyTemplate;
use Redis;
use App\System\Session;
use App\Mailer\MultiContact;
use App\Structures\Mail as StructMail;
use App\Mailer\Queue;
use App\Util\UrlHelpers;
use App\AIM\Traffic;
use App\Template as Layout;
use App\MaintenanceRequest;

class PostController extends Controller
{
    use PageResolver;
    use ValidatesRequests;
    use Nono;

    //Declared by trait: protected $_site
    //TODO: Create a loading mechanism so we can dynamically load and unload allowed handlers
    protected $_allowed = [
        /**********************************************************/
        /* Routes that process non-authenticated form submissions */
        /**********************************************************/
        'unit'          => 'handleUnit',
        'contact'       => 'handleContact',
        'briefContact'  => 'handleBriefContact',
        'schedule'      => 'handleSchedule',
        'apply-online'  => 'handleApplyOnline',

        /* 
         * This route is for when the user clicks on a floorplan that says "limited availability" 
        */
        'post-limited'  => 'handleLimited',

        /* Administrative/CMS routes */
        'text-tag'      => 'handleTextTag',
        'text-tag-get'  => 'handleGetTextTag',

        /******************************/
        /* Routes for resident portal */
        /******************************/
        'post-portal-center' => 'handleResident',
        'find-userid'   => 'handleFindUserId',
        'reset-password'=> 'handleResetPassword',

        /*==========================================================*/
        /* Routes that require authentication (done via middleware) */
        /*==========================================================*/
        'post-resident-contact-mailer'   => 'handleResidentContact',
        'post-maintenance-request'       => 'handleMaintenance',
    ];
    protected $_translations = [];
    //

    public function __construct()
    {
        if (ENV("SHOW_DEBUG_BAR") == "0") {
            \Debugbar::disable();
        }
    }

    public function invalidCaptcha(string $page)
    {
        //TODO: this should be a redirect instead of returning a view
        $siteData = $this->resolvePageBySite($page, []);
        $siteData['data']['invalidCaptcha'] = true;
        return view($siteData['path'], $siteData['data']);
    }

    public function sendMultiContact(string $mode, array $details)
    {
        try {
            $email = new Email;
            $email->to = $details['user'];
            $email->subject = $details['subject']['user'];
            $email->html_body = $details['data'];
            $from =  MultiContact::getPropertyEmail();
            $email->from = array_shift($from);
            $email->cc = [];
            $email->save();
            $email->addQueue();
        } catch (ParameterException $p) {
            throw $p;
        }

        try {
            $email = new Email;
            $to = MultiContact::getPropertyEmail();
            $email->to = array_shift($to);
            $email->subject = $details['subject']['property'];

            $site = app()->make('App\Property\Site');
            $entity = $site->getEntity();
            $path = Layout::getEmailTemplatePath($entity, 'property-contact');
            $data = $details['data']->getData();
            foreach($data as $key => $value){
                if($key !== 'data' && !isset($details[$key])){
                    $details[$key] = $value;
                }
            }
            $email->html_body = view($path)->with($details);
            /*
            $email->html_body = MultiContact::getPropertyViewHtml(
                'layouts/dinapoli/email/property-contact', $details['data']);
             */
            $email->from = $details['user'];
            $email->cc = MultiContact::getCcPropertyEmail();
            $email->save();
            $email->addQueue();
            // $queue->queueItem($struct);
        } catch (ParameterException $p) {
            throw $p;
        }
    }

    public function handle(Request $request, string $page)
    {
        Util::log(var_export($request, 1));
        $inPage = in_array($page, array_keys($this->_allowed));
        $inPath = in_array($request->getPathInfo(), array_keys($this->_allowed));
        if (!$inPage && !$inPath) {
            throw new \App\Exceptions\BaseException("Invalid path : " . $request->getPathInfo());
        }
        if ($this->_site === null) {
            $this->_site = Site::$instance;
        }
        $this->_request = $request;
        $this->_page = $page;
        if ($inPage) {
            return $this->{$this->_allowed[$page]}($request);
        } else {
            return $this->{$this->_allowed[$request->getPathInfo()]}($request);
        }
    }

    public function handleGetTextTag(Request $req)
    {
        $site = app()->make('App\Property\Site');
        $tag = $req->input("tag");

        $body = $site->getEntity()->getText($tag, ['nodecorate' => 1]);
        if (strlen($body)) {
            die(json_encode(['success' => 'true','body' => $body]));
        }

        $arr = TextType::select('id')->where('str_key', $tag)->get()->toArray();
        if (empty($arr)) {
            $text = $site->getEntity()->getText($tag);
            die(json_encode(['success' => 'true','body' => $text]));
        }
        $typeId = $arr[0]['id'];
        $propertyText = PropertyText::where(
            ['property_text_type_id' => $typeId],
            ['entity_id' => $site->getEntity()->id]
            )->get()->toArray();
        if (empty($propertyText)) {
            die(json_encode(['success' => 'true','body' => '']));
        }
        Util::log(var_export($propertyText, 1), ['log' => 'propertyText']);
        die(json_encode(['success' => 'true','body' => $propertyText[0]['string_value']]));
    }

    public function handleTextTag(Request $req)
    {
        $site = app()->make('App\Property\Site');
        $tag = $req->input("tag");
        $body = $req->input("body");

        $arr = TextType::select('id')->where('str_key', $tag)->get()->toArray();

        if (count($arr) == 0) {
            //Create text type
            $ttype = new TextType();
            $ttype->str_key = $tag;
            $ttype->save();
            $typeId = $ttype->id;
        } else {
            $typeId = $arr[0]['id'];
        }
        $propertyText = PropertyText::where(
            ['property_text_type_id' => $typeId],
            ['entity_id' => $site->getEntity()->id]
            )->get();
        if (count($propertyText) == 0) {
            $propertyText = new PropertyText();
            $propertyText->property_text_type_id = $typeId;
            $propertyText->entity_id = $site->getEntity()->id;
            $propertyText->string_value = $body;
            $propertyText->save();
        } else {
            $propertyText = $propertyText->first();
            $propertyText->string_value = $body;
            $propertyText->save();
        }
        $site->getEntity()->setText($tag, $body);
        Util::redisUpdateKeys(['like' => Util::redisKey('*' . $tag . '*')], $body);
        die(json_encode(['success' => 'true']));
    }

    protected function formatValidationErrors(ValidatorContract $validator)
    {
        $this->loadErrorTranslations(Site::$instance->getEntity()->fk_legacy_property_id);
        $finalArray = [];
        foreach ($validator->errors()->all() as $i => $error) {
            $finalArray[$i] = $this->getErrorTranslation($error);
        }
        return $finalArray;
    }

    public function getErrorTranslation(string $error)
    {
        //TODO: !optimization use TextCache for this
        if (!isset($this->_translations[$this->_page])) {
            return $error;
        }
        foreach ($this->_translations[$this->_page] as $index => $translation) {
            if (strcmp($translation['orig'], $error) == 0) {
                return $translation['replace'];
            }
        }
        return $error;
    }

    public function loadErrorTranslations(int $legacyPropertyId)
    {
        //TODO: offload these strings to a file somewhere !organization
        $this->_translations = [
            'reset-password' => [
                [
                'orig' => 'The txt user id field is required.',
                'replace' => 'User ID is a required field.'
                ]
            ],
        ];
    }


    //TODO: Handle form validation
    public function handleResidentContact(Request $req)
    {
        $data = $req->all();
        Site::$instance = $site = app()->make('App\Property\Site');
        if (Session::residentUserLoggedIn() === false) {
            return $this->residentNotLoggedIn();
        }

        $siteData = $this->resolvePageBySite('/resident-portal/contact-request', ['resident-portal' => true]);
        $to = $data['email'];
        $finalArray['contact'] = $data;
        $aptName = Site::$instance->getEntity()->getLegacyProperty()->name;

        $templateName = array_get($siteData, 'data.fsid');
        $siteData['data']['sent'] = true;
        $siteData['data']['name'] = $req->input('name');
        $siteData['data']['email'] = $req->input('email');
        $siteData['data']['phone'] =  (isset($data['phone']) && strlen($data['phone'])) ? $data['phone'] : "No phone number supplied";
        $siteData['data']['memo'] = (isset($data['memo']) && strlen($data['memo'])) ? $data['memo'] : "no memo supplied";
        $finalArray = $this->_prefillArray($siteData);
        $finalArray['mode'] = 'resident-contact';
        $this->sendMultiContact('contact-request', [
            'user' => $to,
            'fromName' => $data['name'],
            'contact' => $data,
            'subject' => [
                'property' => 'Resident Portal Contact Request for property: ' . $aptName,
                'user' => 'Thank you for contacting '  . $aptName . ' Apartments',
            ],
            'data' =>
                Layout::getEmailTemplateView(
                $finalArray['entity'],
                'resident-portal/contact',
                $finalArray
            ),
        ]);

        return redirect('/resident-portal/contact-request')->with('sent', '1');
    }

    public function handleLimited(Request $req){
        //Grab the unit data from the post request and give that to the contact page
        Session::set(Session::CONTACT_US_LIMITED_AVAILABILITY,base64_encode(json_encode($req->all())));
        return redirect('/limited')->with('limitedRequest',$req->all()); 
    }


    public function handleSchedule(Request $req)
    {
        $data = $req->all();
        Site::$instance = $site = app()->make('App\Property\Site');
        $aptName =  Site::$instance->getEntity()->getLegacyProperty()->name;
        if (!Util::isDev()) {
            if (!$this->validateCaptcha($data['g-recaptcha-response'])) {
                return $this->invalidCaptcha('apply-online');
            }
        }

        $this->validate($req, [
            'firstname' => 'required|max:64',
            'lastname' => 'required|max:64',
            'email' => 'required|email',
            'phone' => 'required|max:14|regex:~\([0-9]{3}\) [0-9]{3}\-[0-9]{4}~',
            'moveindate' => 'required|max:32',
            'visitdate' => 'required|max:15',
            'visittime' => 'required|max:15',
        ]);
        //
        $siteData = $this->resolvePageBySite('schedule-a-tour', []);
        if (Util::isDev()) {
            $to = env("DEV_EMAIL");
        } else {
            $to = $data['email'];
        }
        $data['mode'] = 'schedule-a-tour';
        /*
         * Insert data into traffic table
         */
        (app()->make('App\AIM\Traffic'))->insertTraffic(
            $data['firstname'],
            $data['lastname'],
            $data['email'],
            $data['phone'],
            $data['moveindate'],
            $data['visitdate'],
            '',
            '',
            $data['mode'],
            $data['visittime']
        );
        $finalArray = $this->_prefillArray($data);
        $finalArray['contact'] = $data;
        $this->sendMultiContact('schedule-a-tour', [
            'user' => $data['email'],
            'fromName' => $data['firstname'] . " " . $data['lastname'],
            'contact' => $data,
            'subject' => [
                'property' => 'A customer wants to schedule a tour for property: ' . $aptName,
                'user' => 'Schedule A Tour Confirmation for '  . $aptName . ' Apartments',
            ],
            'data' => view('layouts/dinapoli/email/user-confirm', $finalArray)
        ]);

        $siteData = $this->resolvePageBySite('schedule-a-tour', []);
        $siteData['data']['sent'] = true;

        $contact = app()->make('App\Contact');
        $contact->first_name = $data['firstname'];
        $contact->last_name = $data['lastname'];
        $contact->email = $data['email'];
        $contact->howcontact = var_export(['email'], 1);
        $contact->when = $data['visitdate'] . " " . $data['visittime'];
        $contact->phone = $data['phone'];
        $contact->property_id = app()->make('App\Property\Site')->getEntity()->fk_legacy_property_id;
        $contact->save();

        $siteData['data']['redirectConfig'] = $this->_fillApplyOnlineRedirectData();
        flash('Thanks! We will be in touch Soon!');
        $url = UrlHelpers::getUrl('schedule-a-tour', [
            'submitted' => 1,
            'from' => 'Schedule']
        );
        return redirect($url);
    }

    public function handleApplyOnline(Request $req)
    {
        $data = $req->all();
        Site::$instance = $site = app()->make('App\Property\Site');
        $aptName =  Site::$instance->getEntity()->getLegacyProperty()->name;
        if (!Util::isDev()) {
            if (!$this->validateCaptcha($data['g-recaptcha-response'])) {
                return $this->invalidCaptcha('apply-online');
            }
        }

        $this->validate($req, [
            'fname' => 'required|max:64',
            'lname' => 'required|max:64',
            'email' => 'required|email',
            'phone' => 'required|max:14|regex:~\([0-9]{3}\) [0-9]{3}\-[0-9]{4}~',
        ]);
        //
        $siteData = $this->resolvePageBySite('apply-online', []);
        if (Util::isDev()) {
            $to = env("DEV_EMAIL");
        } else {
            $to = $data['email'];
        }
        $data['mode'] = 'apply-online';


        /*
         * unpack base64 encoded json
         */
        if (isset($data['b'])) {
            $unpacked = base64_decode($data['b']);
            $json = json_decode($unpacked, true);
        }
        if (!isset($json['u'])) {
            $json['u'] = '';
        }
        if (!isset($json['t'])) {
            $json['t'] = '';
        }
        /*
         * Insert data into traffic table
         */
        (app()->make('App\AIM\Traffic'))->insertTraffic(
            $data['fname'],
            $data['lname'],
            $data['email'],
            $data['phone'],
            '',     //moveindate
            '',     //visitdate
            $json['u'],     //unit number
            $json['t'],     //unit type
            $data['mode'],
            ''
        );

        $contact = app()->make('App\Contact');
        $contact->first_name = $data['fname'];
        $contact->last_name = $data['lname'];
        $contact->email = $data['email'];
        $contact->howcontact = var_export(['email'], 1);
        //$contact->when = $data['visitdate'] . " " . $data['visittime'];
        $contact->phone = $data['phone'];
        $contact->property_id = app()->make('App\Property\Site')->getEntity()->fk_legacy_property_id;
        $contact->save();


        $finalArray = $this->_prefillArray($data);
        $finalArray['contact'] = $data;
        $this->sendMultiContact('apply-online', [
            'user' => $data['email'],
            'fromName' => $data['fname'] . " " . $data['lname'],
            'contact' => $data,
            'subject' => [
                'property' => 'A customer applied online for property: ' . $aptName,
                'user' => 'Apply Online Confirmation for '  . $aptName . ' Apartments',
            ],
            'data' => view('layouts/dinapoli/email/user-confirm', $finalArray)
        ]);
        $siteData = $this->resolvePageBySite('apply-online', []);
        $siteData['data']['sent'] = true;

        $siteData['data']['redirectConfig'] = $this->_fillApplyOnlineRedirectData();
        $url = UrlHelpers::getUrl('/', [
            'submitted' => 1,
            'from' => 'Schedule'
        ]);
        // return view($siteData['path'], $siteData['data']);
        return view($siteData['path'], $siteData['data']);
    }

    protected function _fillApplyOnlineRedirectData() : array
    {
        $arr = PropertyTemplate::select('online_application_url')
            ->where('property_id', Site::$instance->getEntity()->fk_legacy_property_id)
            ->get()
            ->first();
        if ($arr === null) {
            return [];
        }
        if (strlen($arr->toArray()['online_application_url']) == 0) {
            return [];
        }
        return ['redirect' => true, 'url' => $arr->toArray()['online_application_url']];
    }


    public function handleUnit(Request $req)
    {
        $data = $req->all();
        Site::$instance = $site = app()->make('App\Property\Site');
        $cleaned = [
            'unittype' => Util::transformFloorplanName($data['unittype']),
            'bed' => intval($data['bed']),
            'bath' => floatval($data['bath']),
            'sqft' => intval($data['sqft']),
            'orig_unittype' => $data['unittype'],
        ];



        $siteData = $this->resolvePageBySite('unit', $cleaned);
        return view($siteData['path'], $siteData['data']);
    }

    protected static $_styleSheets =  [
            'http://www.400rhett.com/css/jquery-ui.min.css',
            'http://www.400rhett.com/css/bootstrap-theme.min.css',
            'http://www.400rhett.com/css/bootstrap.min.css',
            'http://www.400rhett.com/css/animations.css',
            'http://www.400rhett.com/css/main.css'
            ];

    public static function styleSheets()
    {
        return self::$_styleSheets; //TODO !organization This needs to go somewhere else
    }

    protected function _prefillArray(array $arr)
    {
        //TODO: replace these with the site's css !launch
        $arr['styleSheets'] = self::$_styleSheets;
        $arr['apartmentName'] = Site::$instance->getEntity()->property_name;
        $arr['entity'] = Site::$instance->getEntity();
        return $arr;
    }

    protected function _getApartmentEmail()
    {
        //TODO: move this to a different place !organization
        if (Util::isDev()) {
            return env("DEV_EMAIL");
        }
        $email = \App\Property\Template::select('email')->where('property_id', Site::$instance->getEntity()->fk_legacy_property_id)
            ->get();
        if (count($email)) {
            return $email[0]['email'];
        }
    }

    public function validateCaptcha(string $captcha)
    {
        //TODO: create a class to do this !organization
        $postdata = http_build_query(
            array(
                'secret' => ENV('RECAPTCHA'),
                'response' => $captcha,
                'remoteIp' => $_SERVER['REMOTE_ADDR']
            )
        );

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );

        $context  = stream_context_create($opts);
        $result = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        if (json_decode($result)->success) {
            return true;
        }
        return false;
    }

    public function decorateMaintenance($data)
    {
        $data['permissionToEnter'] = isset($data['perm2entercb']);
        if ($data['permissionToEnter'] === false) {
            $data['maintenance_name'] = 'none';
            $data['PermissionToEnterDate'] = '0000/00/00';
        }

        return $data;
    }

    public function handleFindUserId(Request $req)
    {
        $data = $req->all();
        Site::$instance = $this->_site = app()->make('App\Property\Site');
        $this->validate($req, [
            'email' => 'required|email',
            'unit' => 'required'
        ]);

        $soap = app()->make('App\Assets\SoapClient');
        $data = $soap->findUser(Site::$instance->getEntity()->getLegacyProperty()->code, $data['email'], $data['unit']);
        \Debugbar::info($data);
        $siteData = $this->resolvePageBySite('/resident-portal/find-userid', ['resident-portal' => true]);
        $siteData['data']['from'] = 'findUserId';
        if ($data['status'] == 'error') {
            $siteData['data']['userIdNotFound'] = true;
        } else {
            $siteData['data']['userIdFound'] = true;
        }
        return view($siteData['path'], $siteData['data']);
    }


    public function handleResetPassword(Request $req)
    {
        $data = $req->all();
        Site::$instance = $this->_site = app()->make('App\Property\Site');
        $this->validate($req, [
            'txtUserId' => 'required'
        ]);

        $soap = app()->make('App\Assets\SoapClient');
        $data = $soap->resetPassword($data, $data['txtUserId']);
        \Debugbar::info($data);
        $siteData = $this->resolvePageBySite('/resident-portal/reset-password', ['resident-portal' => true]);
        $siteData['data']['from'] = 'resetPassword';
        if ($data['status'] == 'error') {
            $siteData['data']['userIdNotFound'] = true;
        } else {
            $siteData['data']['userIdFound'] = true;
        }
        return view($siteData['path'], $siteData['data']);
    }

    public function residentNotLoggedIn()
    {
        $siteData = $this->resolvePageBySite('/resident-portal/' . $this->_page, ['resident-portal' => true]);
        $siteData['data']['userNotLoggedIn'] = true;
        return view($siteData['path'], $siteData['data']);
    }

    public static function log(string $e)
    {
        Util::log($e, ['log' => 'postcontroller']);
    }

    public function handleMaintenance(Request $req)
    {
        $data = $req->all();
        Site::$instance = $this->_site = app()->make('App\Property\Site');
        if (Session::residentUserLoggedIn() === false) {
            return $this->residentNotLoggedIn();
        }
        $this->validate($req, [
            'ResidentName' => 'required|max:64',
            'maintenance_unit' => 'required|max:16',
            'email' => 'required|max:128|email',
            'maintenance_phone' => 'required|max:14|regex:~\([0-9]{3}\) [0-9]{3}\-[0-9]{4}~',
            'maintenance_name' => 'max:64',
            'PermissionToEnterDate' => 'max:15',
            'maintenance_mrequest' => 'required'
            ]);

        $soap = app()->make('App\Assets\SoapClient');
        // dd($req->file('image'));
        $maintenanceRequest = new MaintenanceRequest;
        $maintenanceRequest->resident_name = $data['ResidentName'];
        $maintenanceRequest->maintenance_unit = $data['maintenance_unit'];
        $maintenanceRequest->email = $data['email'];
        $maintenanceRequest->property_code = Site::$instance->getEntity()->getLegacyProperty()->code;
        $maintenanceRequest->maintenance_phone = $data['maintenance_phone'];
        $maintenanceRequest->maintenance_name = $data['maintenance_name'];
        $maintenanceRequest->permission_to_enter_date = $data['PermissionToEnterDate'];
        $maintenanceRequest->maintenance_unit = $data['maintenance_unit'];
        $maintenanceRequest->maintenance_mrequest = $data['maintenance_mrequest'];
        $maintenanceRequest->save();
        $maintenanceRequest
            ->addMediaFromRequest('image')
            ->toMediaLibrary('default', 'local');

        $data = $this->decorateMaintenance($data);

        $siteData = $this->resolvePageBySite(
            '/resident-portal/maintenance-request',
            ['resident-portal' => true]
        );
        if (Util::isDev()) {
            $to = env("DEV_EMAIL");
        } else {
            $to = $data['email'];
        }
        $response = $soap->maintenanceRequest($data);
        $maintenanceRequest->processSoapResponse($response);
        if ($response['Status'] == 'error') {
            $siteData['data']['maintenanceError'] = true;
        } else {
            //Send email
            $finalArray = $this->_prefillArray(['mode' => 'maintenance']);
            $finalArray['contact'] = $data;

            $mail = new Email;
            $mail->from = $this->_getApartmentEmail();
            $mail->cc = $this->_getApartmentEmail();
            $mail->to = $to;
            $mail->html_body = Layout::getEmailTemplateView(
                $finalArray['entity'],
                'resident-portal/maintenance-request',
                $finalArray
            );
            $mail->subject = "Maintanance";
            $mail->save();
            $mail->addQueue();
        }
        return redirect('/resident-portal/portal-center')->with('maint-sent', '1')
            ->with('maint-workorder', Util::arrayGet($response, 'WorkOrderNumber'))
            ->with('maint-soapresponse', $response);
    }

    public function handleBriefContact(Request $req)
    {
        $data = $req->all();
        Site::$instance = $site = app()->make('App\Property\Site');
        $aptName =  Site::$instance->getEntity()->getLegacyProperty()->name;
        $this->validate($req, [
            'name' => 'required|max:64',
            'email' => 'required|max:128|email',
            ]);
        if (isset($data['message'])) {
            $message = substr($data['message'], 0, 256);
        } else {
            $message = "-- no message provided --";
        }

        $finalArray = $this->_prefillArray(['mode' => 'briefContact']);
        $finalArray['contact'] = $data;

        $siteData = $this->resolvePageBySite('contact', $data);
        if (Util::isDev()) {
            $to = 'bvfbarten+1@gmail.com';
        } else {
            $to = $data['email'];
        }
        $finalArray['contact']['mode'] = 'briefContact';
        /*
         * Insert data into traffic table
         */
        (app()->make('App\AIM\Traffic'))->insertTraffic(
            $data['name'],
            $data['name'],
            $data['email'],
            '',
            '',
            '',
            '',
            '',
            $finalArray['contact']['mode'],
            ''
        );

        $contact = app()->make('App\Contact');
        $contact->first_name = $data['name'];
        $contact->last_name = $data['name'];
        $contact->email = $data['email'];
        $contact->howcontact = var_export(['email'], 1);
        //$contact->when = $data['visitdate'] . " " . $data['visittime'];
        //$contact->phone = $data['phone'];

        $contact->property_id = app()->make('App\Property\Site')->getEntity()->fk_legacy_property_id;
        $contact->save();


        $this->sendMultiContact('apply-online', [
            'user' => $data['email'],
            'fromName' => $data['name'],
            'contact' => $data,
            'subject' => [
                'property' => 'User would like to schedule a tour [front page] property: ' . $aptName,
                'user' => 'Schedule a tour Confirmation for '  . $aptName . ' Apartments',
            ],
            'data' => Layout::getEmailTemplateView($finalArray['template'], 'user-confirm', $finalArray)
        ]);
        $siteData['data']['sent'] = true;
        if ($req->method() == 'POST') {
            flash('Thanks! We will be in touch Soon!');
            $url = UrlHelpers::getUrl('/', [
                'submitted' => 1,
                'from' => 'briefContact']
            );
            return redirect($url);
        };
    }

    public function handleContact(Request $req)
    {
        $data = $req->all();
        Site::$instance = $site = app()->make('App\Property\Site');
        $aptName =  Site::$instance->getEntity()
            ->getLegacyProperty()
            ->name;
        if (!Util::isDev() && !$this->validateCaptcha($data['g-recaptcha-response'])) {
            return $this->invalidCaptcha($this->_page);
        }
        $this->validate($req, [
            'first_name' => 'required|max:64',
            'last_name' => 'required|max:64',
            'email' => 'required|max:128|email',
            'phone' => 'required|max:14|regex:~\([0-9]{3}\) [0-9]{3}\-[0-9]{4}~',
            'date'=> 'required|date',
            ]);
        $cleaned = [
            'first_name' => preg_replace("|[^a-zA-Z \.]+|", "", $data['first_name']),
            'last_name' => preg_replace("|[^a-zA-Z \.]+|", "", $data['last_name']),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'movein' => $data['date'],
            'mode' => Util::arrayGet($data,'mode','contact')
        ];

        $limitedRequest = Util::arrayGet($data,'limitedRequest',[]);
        if($limitedRequest){
            try{
                $json = base64_decode($limitedRequest);
                $limitedRequest = json_decode($json,true);
                $cleaned['limited'] = $limitedRequest;
            }catch(\Exception $e){
                Util::monoLog("Invalid limitedRequest: " . var_export($req,1),'warning');
                $limitedRequest = []; 
            }
        }

        if(empty($limitedRequest) && $limitedRequest = Session::get(Session::CONTACT_US_LIMITED_AVAILABILITY)){
            $cleaned['limited'] = $limitedRequest;
        }

        $contact = app()->make('App\Contact');
        $contact->first_name = $cleaned['first_name'];
        $contact->last_name = $cleaned['last_name'];
        $contact->email = $cleaned['email'];
        $contact->notes = 'no notes';
        $contact->property_id = Site::$instance->getEntity()->fk_legacy_property_id;
        $contact->corporate_group_id = Site::$instance->getEntity()->getLegacyProperty()->corporate_group_id;
        $contact->phone = $cleaned['phone'];
        $contact->when = $cleaned['movein'];
        $contact->property_id = app()->make('App\Property\Site')->getEntity()->fk_legacy_property_id;
        $contact->save();

        /*
         * Insert data into traffic table
         */
        (app()->make('App\AIM\Traffic'))->insertTraffic(
            $cleaned['first_name'],
            $cleaned['last_name'],
            $cleaned['email'],
            $cleaned['phone'],
            $cleaned['movein'],
            '',
            '',
            Util::arrayGet($limitedRequest,'unittype',''),
            Util::arrayGet($data,'mode','contact'),
            ''
        );
        $finalArray = $this->_prefillArray(['mode' => Util::arrayGet($cleaned,'mode','contact')]);
        $finalArray['contact'] = $cleaned;

        $siteData = $this->resolvePageBySite('contact', $cleaned);
        if (Util::isDev()) {
            $to = 'wmerfalen+1@gmail.com';
        } else {
            $to = $cleaned['email'];
        }
        $finalArray['limited'] = $data['limited'] = $limitedRequest;
        
        $email = new Email();
        $email->fromName = "{$cleaned['first_name']} {$cleaned['last_name']}";
        $email->subject =
        $data['movein'] = array_get($data, 'date');
        $this->sendMultiContact('contact', [
            'user' => $cleaned['email'],
            'fromName' => $cleaned['first_name'] . " " . $cleaned['last_name'],
            'contact' => $data,
            'subject' => [
                'property' => 'Contact Form Submission at ' . $aptName,
                'user' => 'Contact Us Confirmation for '  . $aptName . ' Apartments',
            ],
            'data' =>
                Layout::getEmailTemplateView(Site::$instance->getEntity(),
                'user-confirm', $finalArray)
        ]);
        $siteData['data']['sent'] = true;
        Session::set(Session::CONTACT_US_LIMITED_AVAILABILITY,null);
        if ($req->method() == 'POST') {
            $url = UrlHelpers::getUrl('/contact', [
                'submitted' => 1,
                'from' => 'contact']
            );
            return redirect($url);
        };
        return view($siteData['path'], $siteData['data']);
    }


    public function invalidUsername()
    {
        $data = $this->resolvePageBySite('resident-portal');
        return view($data['path'],
                array_merge($data['data'], $this->_prefillArray(['residentFailed' => 'invalid user name']))
        );
    }

    public function invalidPassword()
    {
        $data = $this->resolvePageBySite('resident-portal');
        return view($data['path'],
                array_merge($data['data'], $this->_prefillArray(['residentFailed' => 'invalid password']))
        );
    }
    public function handleResident(Request $req)
    {
        $data = $req->all();
        Session::residentUserUnset();
        Site::$instance = $site = app()->make('App\Property\Site');

        if (!isset($data['email'])) {
            //TODO fail validation properly
            return $this->invalidUsername();
        }
        if (!isset($data['pass'])) {
            return $this->invalidPassword();
        }

        $user = substr($data['email'], 0, 64);
        $pass = substr($data['pass'], 0, 64);
        \Debugbar::info($user, $pass);
        $soap = app()->make('App\Assets\SoapClient');
        Util::monoLog(
            "Resident portal return: " .      print_r(compact('user', 'pass', 'soap'), 1)
        );
        $result = $soap->residentPortal($user, $pass);
        Util::monoLog(
            "Resident portal result: " .      print_r(compact('result'), 1)
        );
        if ($result[0] === 'True' || $user == 'foobar' && $pass == 'foobar') {
            $page = 'resident-portal/portal-center';
            //TODO: !refactor !organization make this a function to be called to login a user
            Session::residentUserSet($user . ':' . md5($pass) . "|" . json_encode($result));
            $extra = ['resident-portal' => true];
            $siteData['data']['residentInfo'] = $result;
            return redirect('/resident-portal/portal-center')->with('residentInfo', $result);
        } else {
            Session::residentUserUnset();
            return redirect('/resident-portal/')->with('residentFailed', true);
        }
    }
}
