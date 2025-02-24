<?php
use App\Util\Util;
use App\Property\Template as PropertyTemplate;
use App\Property\Site;

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
							<h1>Terms of service</h1>
							<div class="divder-teal"></div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
  <b>Web Site Terms and Conditions of Use</b><br />
<b>1. Terms</b>
<p>By accessing this web site, you are agreeing to be bound by these web site Terms and Conditions of Use, all applicable laws and regulations, and agree that you are responsible for compliance with any applicable local laws. If you do not agree with any of these terms, you are prohibited from using or accessing this site. The materials contained in this web site are protected by applicable copyright and trade mark law.</p>
<b>2. Use License</b>
<ol type="a" style="font-size:1em; color:black;">
<li style="font-size:1em; color:black;">Permission is granted to temporarily download one copy of the materials (information or software) on <?php echo $entity->getTitle(); ?>&#8217;s web site for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:
<ol type="i" style="font-size:1em; color:black;">
<li style="font-size:1em; color:black;">modify or copy the materials;</li>
<li style="font-size:1em; color:black;">use the materials for any commercial purpose, or for any public display (commercial or non-commercial);</li>
<li style="font-size:1em; color:black;">attempt to decompile or reverse engineer any software contained on <?php echo $entity->getTitle();?>'s web site;</li>
<li style="font-size:1em; color:black;">remove any copyright or other proprietary notations from the materials; or</li>
<li style="font-size:1em; color:black;">transfer the materials to another person or &#8220;mirror&#8221; the materials on any other server.</li>

</ol>
</li>
<li style="font-size:1em; color:black;">This license shall automatically terminate if you violate any of these restrictions and may be terminated by <?php echo $entity->getTitle();?> at any time. Upon terminating your viewing of these materials or upon the termination of this license, you must destroy any downloaded materials in your possession whether in electronic or printed format.</li>
</ol>
<b>3. Disclaimer</b>
<ol type="a" style="font-size:1em; color:black;">
<li style="font-size:1em; color:black;">The materials on <?php echo $entity->getTitle(); ?>&#8217;s web site are provided &#8220;as is&#8221;. <?php echo $entity->getTitle(); ?> makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties, including without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights. Further, <?php echo $entity->getTitle(); ?> does not warrant or make any representations concerning the accuracy, likely results, or reliability of the use of the materials on its Internet web site or otherwise relating to such materials or on any sites linked to this site.</li>
</ol>
<b>4. Limitations</b>
<p>In no event shall <?php echo $entity->getTitle(); ?> or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption,) arising out of the use or inability to use the materials on <?php echo $entity->getTitle(); ?>&#8217;s Internet site, even if <?php echo $entity->getTitle(); ?> or a <?php echo $entity->getTitle(); ?> authorized representative has been notified orally or in writing of the possibility of such damage. Because some jurisdictions do not allow limitations on implied warranties, or limitations of liability for consequential or incidental damages, these limitations may not apply to you.</p>

<b>5. Revisions and Errata</b>
<p>The materials appearing on <?php echo $entity->getTitle(); ?>&#8217;s web site could include technical, typographical, or photographic errors. <?php echo $entity->getTitle(); ?> does not warrant that any of the materials on its web site are accurate, complete, or current. <?php echo $entity->getTitle(); ?> may make changes to the materials contained on its web site at any time without notice. <?php echo $entity->getTitle(); ?> does not, however, make any commitment to update the materials.</p>
<b>6. Links</b>
<p><?php echo $entity->getTitle(); ?> has not reviewed all of the sites linked to its Internet web site and is not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by <?php echo $entity->getTitle(); ?> of the site. Use of any such linked web site is at the user&#8217;s own risk.</p>
<b>7. Site Terms of Use Modifications</b>
<p><?php echo $entity->getTitle(); ?> may revise these terms of use for its web site at any time without notice. By using this web site you are agreeing to be bound by the then current version of these Terms and Conditions of Use.</p>
<b>8. Governing Law</b>
<p>Any claim relating to <?php echo $entity->getTitle(); ?>&#8217;s web site shall be governed by the laws of the State of Utah without regard to its conflict of law provisions.</p>
						
					</div>
				</div>
			</div>
		</section>
        @stop
