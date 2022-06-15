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

				$prod = wc_get_product(get_the_ID());
				$hls = $prod->get_attribute("hls_public");
            $hls_member = $prod->get_attribute("hls_members");
            $hls_premium = $prod->get_attribute("hls_premium");
            $video_preview = $prod->get_attribute("video_preview");
            if(($hls) AND wp_SexHackMe\starts_with('/', $hls)) $hls = site_url().$hls;
            if(($hls_member) AND wp_SexHackMe\starts_with('/', $hls_member)) $hls_member = site_url().$hls_member;
            if(($hls_premium) AND wp_SexHackMe\starts_with('/', $hls_premium)) $hls_premium = site_url().$hls_premium;
            if(($video_preview) AND wp_SexHackMe\starts_with('/', $video_preview)) $video_preview = site_url().$video_preview;
            if (($hls) OR ($hls_member) OR ($hls_premium) OR ($video_preview)) : ?>
             <article id="post-<?php echo get_the_ID();?>" class="post-<?php echo get_the_ID();?> product type-product">
					<header class="entry-header">
						<h2 class="alpha entry-title sexhack_video_title">	
							<?php the_title() ?>
						</h2>		
				   </header><!-- .entry-header -->
               <?php
               //$thumb = wp_get_attachment_image_src(get_post_thumbnail_id( get_the_ID(), 'full' ));
               $thumb = wp_get_attachment_url($prod->get_image_id());

               if($sexhack_pms->is_premium()) 
               {
                  if($hls_premium) echo do_shortcode( "[sexhls url=\"".$hls_premium."\" posters=\"".$thumb."\"]" );
                  else if($hls_member) echo do_shortcode( "[sexhls url=\"".$hls_member."\" posters=\"".$thumb."\"]" );
                  else if($hls) echo do_shortcode( "[sexhls url=\"".$hls."\" posters=\"".$thumb."\"]" );
                  else if($video_preview) echo '<video src='."'$video_preview'".' controls autoplay muted playsinline loop poster="'.$thumb.'"></video>';
                  else echo "Error fetching the video..."; // This should never happen
               }
               elseif($sexhack_pms->is_member()) 
               {
                  if($hls_member) echo do_shortcode( "[sexhls url=\"".$hls_member."\" posters=\"".$thumb."\"]" );
                  else if($hls) echo do_shortcode( "[sexhls url=\"".$hls."\" posters=\"".$thumb."\"]" );
                  else if($video_preview) echo '<video src='."'$video_preview'".' controls autoplay muted playsinline loop poster="'.$thumb.'"></video>';
                  if($hls_premium) echo "Premium versions for subscribers available..";
               }
               else 
               {
                  if($hls) echo do_shortcode( "[sexhls url=\"".$hls."\" posters=\"".$thumb."\"]" );
                  else if($video_preview) echo '<video src='."'$video_preview'".' controls autoplay muted playsinline loop poster="'.$thumb.'"></video>';
                  if($hls_premium) echo "Premium version for subscribers available...";
                  if($hls_member) echo "Members version available...";
               }
               ?>
               </article>
               <?php
           
				else :
					get_template_part( 'content', get_post_format() ); // get_post_format() return empty for us
            endif;
   
            ?>
               <h3><a href="<?php echo get_the_permalink(); ?>">Download the full lenght hi-res version of this video</a><h3>
            <?php

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
