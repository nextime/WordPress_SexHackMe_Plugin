<?php
/**
 * Copyright: 2022 (c)Franco (nextime) Lanza <franco@nexlab.it>
 * License: GNU/GPL version 3.0
 *
 * This file is part of SexHackMe Wordpress Plugin.
 *
 * SexHackMe Wordpress Plugin is free software: you can redistribute it and/or modify it 
 * under the terms of the GNU General Public License as published 
 * by the Free Software Foundation, either version 3 of the License, 
 * or (at your option) any later version.
 *
 * SexHackMe Wordpress Plugin is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License 
 * along with SexHackMe Wordpress Plugin. If not, see <https://www.gnu.org/licenses/>.
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<?php
					//the_archive_title( '<h1 class="page-title">', '</h1>' );
					the_archive_description( '<div class="taxonomy-description">', '</div>' ); // XXX Check it? what it does?
				?>
			</header><!-- .page-header -->

			<?php

			do_action( 'storefront_loop_before' );

         //print_r($sexhack_pms->plans);
			while ( have_posts() ) :
   			the_post();

            echo do_shortcode("[sexadv adv=".get_option('sexadv_video_top')."]");


				$htmltags = '<span><b>TAGS: </b></span>';
            $tags = get_the_terms( get_the_ID(), 'product_tag' );
            if ( ! empty( $tags ) && ! is_wp_error( $tags ) )
            {
               foreach($tags as $tag) {
                  $htmltags.="<span>#".$tag->name . '</span> ';
               }  
            }  

				$videoslug = get_option('sexhack_gallery_slug', 'v');
				$vurl = str_replace("/product/", "/".$videoslug."/", esc_url( get_the_permalink() ));				

				$prod = wc_get_product(get_the_ID());
				$hls = $prod->get_attribute("hls_public");
            $hls_members = $prod->get_attribute("hls_members");
            $hls_premium = $prod->get_attribute("hls_premium");
            $video_preview = $prod->get_attribute("video_preview");
            $gif_preview = $prod->get_attribute("gif_preview");
         	$vr_premium = $prod->get_attribute("vr_premium");
         	$vr_members = $prod->get_attribute("vr_members");
         	$vr_public = $prod->get_attribute("vr_public");
         	$vr_preview = $prod->get_attribute("vr_preview");
         	$categories = explode(", ", wp_SexHackMe\html2text( wc_get_product_category_list($id)));

            if(($hls) AND wp_SexHackMe\starts_with('/', $hls)) $hls = site_url().$hls;
            if(($hls_members) AND wp_SexHackMe\starts_with('/', $hls_members)) $hls_members = site_url().$hls_members;
            if(($hls_premium) AND wp_SexHackMe\starts_with('/', $hls_premium)) $hls_premium = site_url().$hls_premium;
            if(($video_preview) AND wp_SexHackMe\starts_with('/', $video_preview)) $video_preview = site_url().$video_preview;
            if(($vr_public) AND wp_SexHackMe\starts_with('/', $vr_public)) $vr_public = site_url().$vr_public;
            if(($vr_members) AND wp_SexHackMe\starts_with('/', $vr_members)) $vr_members = site_url().$vr_members;
            if(($vr_premium) AND wp_SexHackMe\starts_with('/', $vr_premium)) $vr_premium = site_url().$vr_premium;
            if(($vr_preview) AND wp_SexHackMe\starts_with('/', $vr_preview)) $vr_preview = site_url().$vr_preview;

            $thumb = wp_get_attachment_url($prod->get_image_id());

            $avail = array();
				$tabtext = array('subscribers' => 'Subscribers',
									  'vrsub' => 'Subscribers',
									  'members' => 'Members',
									  'vrmem' => 'Members', 
									  'vrpub' => 'Public',
									  'public' => 'Public');

				if($hls || $video_preview ) $avail[] = 'public';
				if($vr_public || $vr_preview) $avail[] = 'vrpub';
				if($hls_members) $avail[] = 'members';
				if($vr_members) $avail[] = 'vrmem';
				if($hls_premium) $avail[] = 'subscribers';
				if($vr_premium) $avail[] = 'vrsub';					

            $videoaccess = get_query_var('videoaccess', false);
            if($videoaccess && in_array($videoaccess, $avail))
            {
               $tab = $videoaccess;
            } 
				else 
				{
              	if($sexhack_pms->is_premium()) {
                 	if($hls_premium) $tab = 'subscribers';
                 	elseif($vr_premium) $tab = 'vrsub';
                 	elseif($hls_members) $tab = 'members';
                 	elseif($vr_members) $tab = 'vrmem';
                 	elseif($vr_public || $vr_preview) $tab = 'vrpub';
                 	else $tab = 'public';
              	}
              	elseif($sexhack_pms->is_member())  // free membership
              	{
                 	if($hls_members) $tab = 'members';
                 	elseif($vr_members) $tab = 'vrmem';
                 	elseif($vr_public) $tab = 'vrpub';
                 	else $tab = 'public';
              	} 
              	else  // public
              	{
                 	if($vr_public) $tab = 'vrpub';
                 	else $tab = 'public';
              	}
            }
				?>
             <article id="post-<?php echo get_the_ID();?>" class="post-<?php echo get_the_ID();?> product type-product">
               <header class="entry-header">
                  <h2 class="alpha entry-title sexhack_video_title"> 
                     <?php the_title(); echo " (".$tabtext[$tab]." version)"; ?> 
                  </h2>   
               </header><!-- .entry-header -->
               <div class="sexhack-video-container">
            <?php

				if(in_array($tab, $avail)) 
				{
					switch($tab)
					{

						case "members":
						case "vrmem":
							if($sexhack_pms->is_premium() || $sexhack_pms->is_member())
							{
			               if($hls_members) echo do_shortcode( "[sexhls url=\"".$hls_members."\" posters=\"".$thumb."\"]" );
                  	   else if($vr_members) echo do_shortcode( "[sexvideo url=\"".$vr_members."\" posters=\"".$thumb."\"]" );
							   else echo "<h3 class='sexhack-videonotify'>SOMETHING WENT BADLY WRONG. I CAN'T FIND THE VIDEO</h3>";
							}
							else
                     {
                        if($gif_preview) echo '<img class="sexhack_videopreview" src="'.$gif_preview.'" loading="lazy"></img>';
                        else echo '<img class="sexhack_videopreview" src="'.$thumb.'" loading="lazy"></img>';
								echo "<h3 class='sexhack-videonotify'><a href='/login'>YOU NEED TO LOGIN TO ACCESS THIS VIDEO</a></h3>";
                        echo "<div style='width: 80%; margin-left: 10%;' >".do_shortcode('[pms-login redirect_url="/account" ]')."</div>";
							}
							break;

						case "subscribers":
						case "vrsub":
							if($sexhack_pms->is_premium())
							{
                  		if($hls_premium) echo do_shortcode( "[sexhls url=\"".$hls_premium."\" posters=\"".$thumb."\"]" );
                  		else if($vr_premium) echo do_shortcode( "[sexvideo url=\"".$vr_premium."\" posters=\"".$thumb."\"]" );
								else echo "<h3  class='sexhack-videonotify'>SOMETHING WENT BADLY WRONG. I CAN'T FIND THE VIDEO</h3>";

							}
							else
                     {
                        if($gif_preview) echo '<img class="sexhack_videopreview" src="'.$gif_preview.'" loading="lazy"></img>';
                        else echo '<img class="sexhack_videopreview" src="'.$thumb.'" loading="lazy"></img>';
								echo "<h3 class='sexhack-videonotify'><a href='/product-category/subscriptions/'>YOU NEED A SUBSCRIPTION TO ACCESS THIS VIDEO</a></h3>";
							}
							break;
						
						case "vrpub":
						default:  // public too!
							if($hls) echo do_shortcode( "[sexhls url=\"".$hls."\" posters=\"".$thumb."\"]" );
              			else if($vr_public) echo do_shortcode( "[sexvideo url=\"".$vr_public."\" posters=\"".$thumb."\"]" );
                 		else if($video_preview) echo '<video src='."'$video_preview'".' controls autoplay muted playsinline loop poster="'.$thumb.'"></video>';
                 		else if($vr_preview) echo do_shortcode( "[sexvideo url=\"".$vr_preview."\" posters=\"".$thumb."\"]" );
                 		else if($gif_preview) echo '<img class="sexhack_videopreview" src="'.$gif_preview.'" loading="lazy"></img>';
                 		else echo '<img class="sexhack_videopreview" src="'.$thumb.'" loading="lazy"></img>';
					}
				}
				else  // if(in_array($tab, $avail))
				{
					if($video_preview) echo '<video src='."'$video_preview'".' controls autoplay muted playsinline loop poster="'.$thumb.'"></video>';
					else if($vr_preview) echo do_shortcode( "[sexvideo url=\"".$vr_preview."\" posters=\"".$thumb."\"]" );
					else if($gif_preview) echo '<img class="sexhack_videopreview" src="'.$gif_preview.'" loading="lazy"></img>';
					else echo '<img class="sexhack_videopreview" src="'.$thumb.'" loading="lazy"></img>';
					?>
						<h2 class='sexhack-videonotify'><b>PUBLIC VIDEO NOT AVAILABLE</b></h2>
					<?php
				}
         ?>
         </div> <!-- video container -->
			<div class="sexhack-tabs">
			<?php
				foreach($avail as $vaval) 
				{
					if(!(in_array('vrpub', $avail) && ($vaval == 'public'))) {
						$hrefurl = $vurl.'/'.$vaval.'/';
						$bclass = '';
						if(isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) $hrefurl .= '?'.$_SERVER['QUERY_STRING'];
						if($vaval == $tab) $bclass='sexhack_toggled_button';
			
					?>
						<a class="sexhack_tab_a" href="<?php echo $hrefurl; ?>">
							<button name="<?php echo $vaval; ?>" class="sexhack_tab_button <?php echo $bclass; ?>">
								<?php echo $tabtext[$vaval]; ?>
							</button>
						</a>
					<?php
					}
				}
			?>
         </div>
			<br><hr>
			<?php 
			echo $htmltags;
			?>

            <h3><a href="<?php echo get_the_permalink(); ?>">Download the full lenght hi-res version of this video</a><h3>

         <hr>
<?php
               echo do_shortcode("[sexadv adv=".get_option('sexadv_video_bot')."]");      
			endwhile;
      
			/**
			 * Functions hooked in to storefront_paging_nav action
			 *
			 * @hooked storefront_paging_nav - 10
			 */
			do_action( 'storefront_loop_after' );


		else :

			get_template_part( 'content', 'none' );

		endif;
		?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
do_action( 'storefront_sidebar' );
get_footer();
