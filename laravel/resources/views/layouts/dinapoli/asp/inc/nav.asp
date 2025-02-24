<% 
' Database connection

Set dbConnection = Server.CreateObject( "ADODB.Connection" )
dbConnection.Open "Driver={SQL Server};" & _
           "Server=192.168.1.139;" & _
           "Address=rentegisql1,1433;" & _
           "Network=DBMSSOCN;" & _
           "Database=AIM_164MTB;" & _
           "Uid=sa;" & _
           "Pwd=mdb121bdm(("
 

dim sqlWebsiteSpecials
dim objRSWebsiteSpecials

sqlWebsiteSpecials = "WS_SP_MAPTS_GET_WEBSITE_SPECIALS"
'WHERE U_MARKETING_NAME IN ('SpecialWebsite','SpecialElse') 
set objRSWebsiteSpecials = dbConnection.Execute( sqlWebsiteSpecials )

do until objRSWebsiteSpecials.eof

U_MARKETING_NAME = objRSWebsiteSpecials( "U_MARKETING_NAME" )
EXPIRATIONDATE = objRSWebsiteSpecials( "EXPIRATIONDATE" )
SPECIAL_TEXT = objRSWebsiteSpecials( "SPECIAL_TEXT" )

dim SpecialWebsite, SpecialStudio, SpecialOneBedroom, SpecialTwoBedroom, SpecialThreeBedroom, SpecialElse
if U_MARKETING_NAME = "SpecialWebsite" then
    SpecialWebsite = SPECIAL_TEXT 
elseif U_MARKETING_NAME = "SpecialElse" then
    SpecialElse = SPECIAL_TEXT 
end if

objRSWebsiteSpecials.movenext
loop

%>
			<!-- Speacials Dropdown -->
            <%if SpecialWebsite = "" then%>
                <!-- No Special -->
            <%else%>
                <%if  Request.ServerVariables("PATH_INFO") = "/index.asp" then%>
                <div id="banner-special">
                	<div class="container relative">
                		<div class="row">
            				<div class="col-md-12 text-center">
        						<div class="text">
        							<b><%=SpecialWebsite%></b><br>
        							<a href="floor-plans.asp" class="btn btn-mod btn-border-w btn-round">
                                 	  FIND OUT MORE
                            		</a> 
        						</div>
                                <a href="#" class="fa fa-times-circle" id="banner-special-close"></a>    				
    						</div>
            			</div>
            		</div>
    	        </div>
                <%else%>
                <%end if%>
            <%end if%>
            <div class="top-nav">
                <div class="full-wrapper relative clearfix">
                    <div class="row">
                        <div class="col-xs-6">
                            <ul class="top-nav-left">
                                <li class="hidden-sm hidden-xs"><i class="fa fa-phone"></i> <b>Call Today</b> : (702) 435-4305</li>
                                <li class="hidden-sm hidden-xs"><i class="fa fa-map-marker"></i> <b>Location</b> : 3000 High View Drive Henderson, NV 89014</li>
                                <li class="hidden-md hidden-lg"><a href="tel:+7025666344" class="gray"><i class="fa fa-phone"></i> Call Us</a></li>
                            </ul>
                        </div>
                        <div class="col-xs-6 text-right">
                            <ul class="top-nav-right">
                                <li class="hidden-sm hidden-xs"><a href="floor-plans.asp"><i class="fa fa-search"></i> Apartment Search</a></li>
                                <li class="hidden-sm hidden-xs"><a href="resident-portal.asp"><i class="fa fa-user"></i> Residents</a></li>
                                <li><a href="floor-plans.asp" class="brown"><b>Apply Now <i class="fa fa-angle-right"></i></b></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
			<nav id="nav" class="main-nav js-stick">
                <div class="full-wrapper relative clearfix">
                    <div class="nav-logo-wrap local-scroll">
                        <a href="index.asp" class="logo">
                            <img src="img/logo.png" alt="" />
                        </a>
                    </div>
                    <div class="mobile-nav">
                        <i class="fa fa-bars"></i>
                    </div>
                    <!-- Main Menu -->
                    <div class="inner-nav desktop-nav">
                        <ul class="clearlist scroll-nav local-scroll">
                            <li class="active hidden-md hidden-sm"><a href="index.asp">Home</a></li>
                            <li><a href="gallery.asp">Gallery</a></li>
                            <li><a href="amenities.asp">Amenities</a></li>
                            <li><a href="floor-plans.asp">Floor Plans</a></li>
                            <li><a href="neighborhood.asp">Neighborhood</a></li>
                            <li class="hidden-md hidden-sm"><a href="contact.asp">Contact</a></li>
                            <li class="hidden-md hidden-lg"><a href="floor-plans.asp">Apartment Search</a></li>
                            <li class="hidden-md hidden-lg"><a href="resident-portal.asp">Residents</a></li> 
                            <li><a href="schedule-a-tour.asp" class="red"><b>Schedule a Tour <i class="fa fa-angle-right"></i></b></a></li>
                        </ul>
                    </div>
                </div>
            </nav>
