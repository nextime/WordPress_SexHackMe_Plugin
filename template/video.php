<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package storefront
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

				$prod = wc_get_product(get_the_ID());
				$hls = $prod->get_attribute("hls_public");
            $hls_member = $prod->get_attribute("hls_members");
            $hls_premium = $prod->get_attribute("hls_premium");
            $video_preview = $prod->get_attribute("video_preview");
            $gif_preview = $prod->get_attribute("gif_preview");
         	$vr_premium = $prod->get_attribute("vr_premium");
         	$vr_member = $prod->get_attribute("vr_members");
         	$vr_public = $prod->get_attribute("vr_public");
         	$vr_preview = $prod->get_attribute("vr_preview");
         	$categories = explode(", ", wp_SexHackMe\html2text( wc_get_product_category_list($id)));

            if(($hls) AND wp_SexHackMe\starts_with('/', $hls)) $hls = site_url().$hls;
            if(($hls_member) AND wp_SexHackMe\starts_with('/', $hls_member)) $hls_member = site_url().$hls_member;
            if(($hls_premium) AND wp_SexHackMe\starts_with('/', $hls_premium)) $hls_premium = site_url().$hls_premium;
            if(($video_preview) AND wp_SexHackMe\starts_with('/', $video_preview)) $video_preview = site_url().$video_preview;
            if(($vr_public) AND wp_SexHackMe\starts_with('/', $vr_public)) $vr_public = site_url().$vr_public;
            if(($vr_member) AND wp_SexHackMe\starts_with('/', $vr_member)) $vr_member = site_url().$vr_member;
            if(($vr_premium) AND wp_SexHackMe\starts_with('/', $vr_premium)) $vr_premium = site_url().$vr_premium;
            if(($vr_preview) AND wp_SexHackMe\starts_with('/', $vr_preview)) $vr_preview = site_url().$vr_preview;

            //if (($hls) OR ($hls_member) OR ($hls_premium) OR ($video_preview) OR ($gif_preview)) : ?>
             <article id="post-<?php echo get_the_ID();?>" class="post-<?php echo get_the_ID();?> product type-product">
					<header class="entry-header">
						<h2 class="alpha entry-title sexhack_video_title">	
							<?php the_title() ?>
						</h2>		
				   </header><!-- .entry-header -->
               <?php
               //$thumb = wp_get_attachment_image_src(get_post_thumbnail_id( get_the_ID(), 'full' ));
               $thumb = wp_get_attachment_url($prod->get_image_id());

               $endhtml = '<br><hr>';
               if($sexhack_pms->is_premium()) 
               {
                  if($hls_premium) echo do_shortcode( "[sexhls url=\"".$hls_premium."\" posters=\"".$thumb."\"]" );
                  else if($vr_premium) echo do_shortcode( "[sexvideo url=\"".$vr_premium."\" posters=\"".$thumb."\"]" );
                  else if($hls_member) echo do_shortcode( "[sexhls url=\"".$hls_member."\" posters=\"".$thumb."\"]" );
                  else if($vr_member) echo do_shortcode( "[sexvideo url=\"".$vr_member."\" posters=\"".$thumb."\"]" );
                  else if($hls) echo do_shortcode( "[sexhls url=\"".$hls."\" posters=\"".$thumb."\"]" );
                  else if($vr_public) echo do_shortcode( "[sexvideo url=\"".$vr_public."\" posters=\"".$thumb."\"]" );
                  else if($video_preview) echo '<video src='."'$video_preview'".' controls autoplay muted playsinline loop poster="'.$thumb.'"></video>';
                  else if($vr_preview) echo do_shortcode( "[sexvideo url=\"".$vr_preview."\" posters=\"".$thumb."\"]" );
                  else if($gif_preview) echo '<img src="'.$gif_preview.'" loading="lazy"></img>';
                  else echo '<img src="'.$thumb.'" loading="lazy"></img>';

               }
               elseif($sexhack_pms->is_member())  // free membership
               {
                  if($hls_member) echo do_shortcode( "[sexhls url=\"".$hls_member."\" posters=\"".$thumb."\"]" );
                  else if($vr_member) echo do_shortcode( "[sexvideo url=\"".$vr_member."\" posters=\"".$thumb."\"]" );
                  else if($hls) echo do_shortcode( "[sexhls url=\"".$hls."\" posters=\"".$thumb."\"]" );
                  else if($vr_public) echo do_shortcode( "[sexvideo url=\"".$vr_public."\" posters=\"".$thumb."\"]" );
                  else if($video_preview) echo '<video src='."'$video_preview'".' controls autoplay muted playsinline loop poster="'.$thumb.'"></video>';
                  else if($vr_preview) echo do_shortcode( "[sexvideo url=\"".$vr_preview."\" posters=\"".$thumb."\"]" );
                  else if($gif_preview) echo '<img src="'.$gif_preview.'" loading="lazy"></img>';
                  else echo '<img src="'.$thumb.'" loading="lazy"></img>';

                  if($hls_premium) $endhtml .= "<h3><a href='/product-category/subscriptions/'>Premium full lenght version available here</a></h3>";
                  if($vr_premium) $endhtml .= "<h3><a href='/product-category/subscriptions/'>VR 3D Premium full lenght version available here</a></h3>";
               }
               else  // public
               {
                  if($hls) echo do_shortcode( "[sexhls url=\"".$hls."\" posters=\"".$thumb."\"]" );
                  else if($vr_public) echo do_shortcode( "[sexvideo url=\"".$vr_public."\" posters=\"".$thumb."\"]" );
                  else if($video_preview) echo '<video src='."'$video_preview'".' controls autoplay muted playsinline loop poster="'.$thumb.'"></video>';
                  else if($vr_preview) echo do_shortcode( "[sexvideo url=\"".$vr_preview."\" posters=\"".$thumb."\"]" );
                  else if($gif_preview) echo '<img src="'.$gif_preview.'" loading="lazy"></img>';
                  else echo '<img src="'.$thumb.'" loading="lazy"></img>';

                  if($hls_premium) $endhtml .= "<h3><a href='/product-category/subscriptions/'>Premium full lenght version available here</a></h3>";
                  if($hls_member) $endhtml .=  "<h3><a href='/login'>Free members only version available! Sign up or Login to watch</a></h3>";
                  if($vr_premium) $endhtml .= "<h3><a href='/product-category/subscriptions/'>VR 3D Premium full lenght version available here</a></h3>";
                  if($vr_member) $endhtml .=  "<h3><a href='/login'>VR 3D Free members only version available! Sign up or Login to watch</a></h3>";

               }
               ?>


               <?php
				//else :
				//	get_template_part( 'content', get_post_format() ); // get_post_format() return empty for us
            //endif;
               echo $endhtml;
            ?>
               <h3><a href="<?php echo get_the_permalink(); ?>">Download the full lenght hi-res version of this video</a><h3>
            <br><hr>
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
