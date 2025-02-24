<?php
use App\Util\Util;
use App\Property\Template as PropertyTemplate;
use App\Property\Site;

try {
    $specials = app()->make('App\Property\Specials');
    $spec = $specials->fetchAllSpecials();
} catch (\Exception $e) {
    $specials = $spec = null;
}
$displayOptions['dont-show-contact-details'] = true;
?>
    @extends('layouts/bascom/main')
        @section('content')
       <!-- Content -->
		<section class="content">
			<!-- Content Blocks -->
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="page-title">
							<h1>Privacy policy</h1>
							<div class="divder-teal"></div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<p><b>Privacy Policy Statement</b></p>
    

  <p>This is the web site of <strong><?php echo $entity->getTitle(); ?></strong>.</p>
<p>Our postal address is<br />
<strong>
<?php echo $entity->getFullAddress();?><br />
<?php echo $entity->getPhone();?><br />
</strong></p>
<p>We can be reached via e-mail at <a href="mailto:<?php echo $entity->getEmail();?>"><?php echo $entity->getEmail();?></a></p>
<p>For each visitor to our Web page, our Web server automatically recognizes the consumer&#8217;s domain name and e-mail address (where possible).</p>
<p>We collect the domain name and e-mail address (where possible) of visitors to our Web page, the e-mail addresses of those who post messages to our bulletin board, the e-mail addresses of those who communicate with us via e-mail, the e-mail addresses of those who make postings to our chat areas, aggregate information on what pages consumers access or visit, user-specific information on what pages consumers access or visit, information volunteered by the consumer, such as survey information and/or site registrations, name and address, telephone number, fax number, payment information (e.g., credit card number and billing address).</p>
<p>The information we collect is used to improve the content of our Web page, used to customize the content and/or layout of our page for each individual visitor, used to notify consumers about updates to our Web site, shared with agents or contractors who assist in providing support for our internal operations, used by us to contact consumers for marketing purposes, shared with other reputable organizations to help them contact consumers for marketing purposes, disclosed when legally required to do so, at the request of governmental authorities conducting an investigation, to verify or enforce compliance with the policies governing our Website and applicable laws or to protect against misuse or unauthorized use of our Website, to a successor entity in connection with a corporate merger, consolidation, sale of assets or other corporate change respecting the Website.</p>
<p>With respect to cookies: We use cookies to store visitors preferences, record session information, such as items that consumers add to their shopping cart, record user-specific information on what pages users access or visit, alert visitors to new areas that we think might be of interest to them when they return to our site, record past activity at a site in order to provide better service when visitors return to our site , ensure that visitors are not repeatedly sent the same banner ads, customize Web page content based on visitors&#8217; browser type or other information that the visitor sends.</p>
<p>If you do not want to receive e-mail from us in the future, please let us know by sending us e-mail at the above address.</p>

<p>From time to time, we make the e-mail addresses of those who access our site available to other reputable organizations whose products or services we think you might find interesting. If you do not want us to share your e-mail address with other companies or organizations, please let us know by sending us e-mail at the above address.</p>
<p>From time to time, we make our <em>customer</em> e-mail list available to other reputable organizations whose products or services we think you might find interesting. If you do not want us to share your e-mail address with other companies or organizations, please let us know by sending us e-mail at the above address.</p>
<p>If you supply us with your postal address on-line you may receive periodic mailings from us with information on new products and services or upcoming events. If you do not wish to receive such mailings, please let us know by sending us e-mail at the above address.</p>
<p>you may receive mailings from other reputable companies. You can, however, have your name put on our do-not-share list by sending us e-mail at the above address.</p>
<p>Please provide us with your exact name and address. We will be sure your name is removed from the list we share with other organizations</p>
<p>Persons who supply us with their telephone numbers on-line may receive telephone contact from us with information regarding new products and services or upcoming events. If you do not wish to receive such telephone calls, please let us know by sending us e-mail at the above address.</p>
<p>Persons who supply us with their telephone numbers on-line may receive telephone contact from other reputable companies. You can, however, have your name put on our do-not-share list by , sending us e-mail at the above address.</p>

<p>Please provide us with your name and phone number. We will be sure your name is removed from the list we share with other organizations.</p>
<p>From time to time, we may use customer information for new, unanticipated uses not previously disclosed in our privacy notice. If our information practices change at some time in the future we will post the policy changes to our Web site to notify you of these changes and we will use for these new purposes only data collected from the time of the policy change forward. If you are concerned about how your information is used, you should check back at our Web site periodically.</p>
<p>Customers may prevent their information from being used for purposes other than those for which it was originally collected by e-mailing us at the above address.</p>
<p>If you feel that this site is not following its stated information policy, you may contact us by sending us e-mail at the above address.</p>
						
					</div>
				</div>
			</div>
		</section> 
        @stop
