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


namespace wp_SexHackMe;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


$sh_video = get_query_var('sh_video', false);
if(!$sh_video) {
   wp_redirect(get_permalink(get_option('sexhack_video404_page', '0')));
   exit ;
}

$video = sh_get_video_from_slug($sh_video);
if(!$video) 
{
    wp_redirect(get_permalink(get_option('sexhack_video404_page', '0')));
   exit ;

}

get_header(); ?>

   <div id="primary" class="content-area">
      <main id="main" class="site-main" role="main">
      <?php if ( have_posts() ) : ?>

         <header class="page-header">
            <?php
               //the_archive_title( '<h1 class="page-title">', '</h1>' );
               //the_archive_description( '<div class="taxonomy-description">', '</div>' ); // XXX Check it? what it does
            ?>
         </header><!-- .page-header -->

         <?php

         do_action( 'storefront_loop_before' );

         //print_r($sexhack_pms->plans);
         while ( have_posts() ) :
            the_post();

            echo do_shortcode("[sexadv adv=".get_option('sexadv_video_top')."]");


            $htmltags = '<span><b>TAGS: </b></span>';
            $tags = $video->get_tags();
            if ( ! empty( $tags ) && ! is_wp_error( $tags ) )
            {
               foreach($tags as $tag) {
                  $htmltags.="<span>#".$tag->tag . '</span> ';
               }  
            }  

            $videoslug = get_option('sexhack_gallery_slug', 'v');
            $vurl = "/".$videoslug."/".$sh_video."/";    

            $prod = $video->get_product();

            $hls_public = $video->hls_public;
            $hls_members = $video->hls_members;
            $hls_premium = $video->hls_premium;
            $video_preview = $video->preview;
            $gif_preview = $video->gif_small;
            $gif = $video->gif;
            $premium_is_ppv = false;
            if($video->premium_is_ppv=='Y') $premium_is_ppv=true;


            $categories = $video->get_categories(true);

            if(($hls_public) AND starts_with('/', $hls_public)) $hls_public = site_url().$hls_public;
            if(($hls_members) AND starts_with('/', $hls_members)) $hls_members = site_url().$hls_members;
            if(($hls_premium) AND starts_with('/', $hls_premium)) $hls_premium = site_url().$hls_premium;
            if(($video_preview) AND starts_with('/', $video_preview)) $video_preview = site_url().$video_preview;

            $t = $video->thumbnail;
            if(is_numeric($t) )
               $thumb = wp_get_attachment_url($video->thumbnail);
            else
               $thumb = $t;

            $avail = array();
            $tabtext = array('subscribers' => 'Subscribers',
               'members' => 'Members',
               'public' => 'Public');
            if($premium_is_ppv) $tabtext['subscribers'] = 'PPV';

            if($hls_public || $video_preview ) $avail[] = 'public';
            if($hls_members) $avail[] = 'members';
            if($hls_premium) $avail[] = 'subscribers';
            

            $videoaccess = get_query_var('videoaccess', false);
            if($videoaccess && in_array($videoaccess, $avail))
            {
               $tab = $videoaccess;
            } 
            else 
            {
                 if(user_has_premium_access() || $video->user_bought_video()) {
                    if($hls_premium && ((!$premium_is_ppv) || $video->user_bought_video() )) $tab = 'subscribers';
                    elseif($hls_members) $tab = 'members';
                    else $tab = 'public';
                 }
                 elseif(user_has_member_access())  // free membership
                 {
                    if($hls_members) $tab = 'members';
                    else $tab = 'public';
                 } 
                 else  // public
                 {
                    $tab = 'public';
                 }
            }
            ?>
             <article id="post-<?php echo get_the_ID();?>" class="post-<?php echo get_the_ID();?> product type-product">
               <div class="sexhack-video-container">
            <?php
            $filterurl=false;
            if(get_option('sexhack_shmdown', false)) $filterurl=get_option('sexhack_shmdown_uri', false);
            if(in_array($tab, $avail)) 
            {
               switch($tab)
               {

                  case "members":
                     if(user_has_member_access() || $video->user_bought_video())
                     {
                        if($filterurl && $hls_members && $video->video_type=="VR" )
                           echo do_shortcode( "[sexvideo url=\"".wp_nonce_url($filterurl.$sh_video."/members/".basename($hls_members), 'shm_members_video-'.$video->id)."\" posters=\"".$thumb."\"]" );
                        else if($hls_members && $video->video_type=="VR" ) 
                           echo do_shortcode( "[sexvideo url=\"".$hls_members."\" posters=\"".$thumb."\"]" );
                        else if($filterurl && $hls_members)
                           echo do_shortcode( "[sexhls url=\"".wp_nonce_url($filterurl.$sh_video."/members/".basename($hls_members), 'shm_members_video-'.$video->id)."\" posters=\"".$thumb."\"]" );
                        else if($hls_members) 
                           echo do_shortcode( "[sexhls url=\"".$hls_members."\" posters=\"".$thumb."\"]" );
                        else echo "<h3 class='sexhack-videonotify'>SOMETHING WENT BADLY WRONG. I CAN'T FIND THE VIDEO</h3>";
                     }
                     else
                     {
                        if($gif_preview) echo '<img class="sexhack_videopreview" src="'.$gif_preview.'" loading="lazy"></img>';
                        else echo '<img class="sexhack_videopreview" src="'.$thumb.'" loading="lazy"></img>';
                     }
                     break;

                  case "subscribers":
                     if((user_has_premium_access() && !$premium_is_ppv) || $video->user_bought_video())
                     {
                        if($filterurl && $hls_premium && $video->video_type=="VR")
                           echo do_shortcode( "[sexvideo url=\"".wp_nonce_url($filterurl.$sh_video."/premium/".basename($hls_premium), 'shm_premium_video-'.$video->id)."\" posters=\"".$thumb."\"]" );
                        else if($hls_premium && $video->video_type=="VR") 
                           echo do_shortcode( "[sexvideo url=\"".$hls_premium."\" posters=\"".$thumb."\"]" );
                        else if($filterurl && $hls_premium) 
                           echo do_shortcode( "[sexhls url=\"".wp_nonce_url($filterurl.$sh_video."/premium/".basename($hls_premium), 'shm_premium_video-'.$video->id)."\" posters=\"".$thumb."\"]" );
                        else if($hls_premium) 
                           echo do_shortcode( "[sexhls url=\"".$hls_premium."\" posters=\"".$thumb."\"]" );
                        else echo "<h3  class='sexhack-videonotify'>SOMETHING WENT BADLY WRONG. I CAN'T FIND THE VIDEO</h3>";

                     }
                     else
                     {
                        if($gif_preview) echo '<img class="sexhack_videopreview" src="'.$gif_preview.'" loading="lazy"></img>';
                        else echo '<img class="sexhack_videopreview" src="'.$thumb.'" loading="lazy"></img>';
                     }
                     break;
                  
                  default:  // public too!
                       if($filterurl && $hls_public && $video->video_type=='VR') 
                          echo do_shortcode( "[sexvideo url=\"".wp_nonce_url($filterurl.$sh_video."/public/".basename($hls_public), 'shm_public_video-'.$video->id)."\" posters=\"".$thumb."\"]" );
                       else if($hls_public && $video->video_type=='VR') 
                          echo do_shortcode( "[sexvideo url=\"".$hls_public."\" posters=\"".$thumb."\"]" );
                       else if($filterurl && $hls_public) 
                          echo do_shortcode( "[sexhls url=\"".wp_nonce_url($filterurl.$sh_video."/public/".basename($hls_public), 'shm_public_video-'.$video->id)."\" posters=\"".$thumb."\"]" );
                       else if($hls_public)  
                          echo do_shortcode( "[sexhls url=\"".$hls_public."\" posters=\"".$thumb."\"]" );
                       else if($video_preview) {
                          //echo do_shortcode( "[sexvideo url=\"".$video_preview."\" posters=\"".$thumb."\"]" );
                          // XXX BUG: sexvideo doesn't like google.drive.com/uc? videos for cross-site problems?
                          echo '<video src='."'$video_preview'".' controls autoplay muted playsinline loop></video></div></div>';
                          header("Access-Control-Allow-Origin: *");
                       }
                       else if($gif_preview) echo '<img class="sexhack_videopreview" src="'.$gif_preview.'" loading="lazy"></img>';
                       else echo '<img class="sexhack_videopreview" src="'.$thumb.'" loading="lazy"></img>';
               }
            }
            else  // if(in_array($tab, $avail))
            {
               if($video_preview) echo '<video src='."'$video_preview'".' controls autoplay muted playsinline loop poster="'.$thumb.'"></video>';
               else if($gif_preview) echo '<img class="sexhack_videopreview" src="'.$gif_preview.'" loading="lazy"></img>';
               else echo '<img class="sexhack_videopreview" src="'.$thumb.'" loading="lazy"></img>';
               ?>
                  <h2 class='sexhack-videonotify' style="padding:5%;"><b>LOGIN TO WATCH THIS VIDEO</b></h2>
                  
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
               <header class="entry-header">
                  <h2 class="sexhack_video_title"> 
                     <?php echo $video->get_title(); echo " (".$tabtext[$tab]." version)"; ?> 
                  </h2>
               </header><!-- .entry-header -->
               <p><?php echo do_shortcode($video->description); ?></p>
               <?php
               $pcontent = do_shortcode(get_post()->post_content);
               if($pcontent!='') {  ?>
               <p><?php echo $pcontent; ?></p>
               <?php } ?>
         <hr>
         <?php 
         echo $htmltags;
         ?>
         <?php if(!$video->user_bought_video() && $hls_premium && ($premium_is_ppv && user_is_premium() || !user_is_premium())) { ?>
            <h3><a href="<?php echo \wc_get_checkout_url()."?add-to-cart=".strval($video->product_id)."&shm_direct_checkout=".strval($video->product_id); ?>"><button>Buy unlimited access to this video</button></a></h3>
         <?php } ?>
         
         <?php if(!$video->user_bought_video() && $hls_premium && !$premium_is_ppv && !user_is_premium() && user_is_member()) { ?>
           <h3><a href="/product-category/subscriptions/"><button>Upgrade to access premium versions</button></a></h3>
         <?php } ?>

         <?php if($video->has_downloads()) { // XXX IS already bought this should redirect to the video ?>
            <?php if(!$video->user_bought_video()) { ?>
            <h3><a href="<?php echo \wc_get_checkout_url()."?add-to-cart=".strval($video->product_id)."&shm_direct_checkout=".strval($video->product_id); ?>"><button>Download full version</button></a></h3>
            <?php } else { ?>
            <h3><a href="<?php echo  get_permalink($video->product_id);?>"><button>Download</button></a></h3>
            <?php } ?>
         <?php } ?>
         <?php if(!user_has_member_access()) { ?>
                 <div class='sexhack-videonotify' style="padding:1px; margin: 0 auto; margin-left: 10%;">
                     <a href="/register/">
                     <img src="<?php echo plugin_dir_url(__FILE__)."/img/cropped-sexhack_members-300x99.jpg";?>"  alt="Register for free and watch members version!" />
                     </a>
                  </div>
         <?php } ?>
			<?php
				if(!is_user_logged_in())
				 echo "<div class='shm_video_login' style='max-width: 300px; width: 80%; margin-left: 10%;' ><h4>Already a member? Login:</h4></div>";
				 echo "<div class='shm_video_login' style='max-width: 300px; width: 80%; margin-left: 10%;' >".do_shortcode('[pms-login redirect_url="/account" ]')."</div>";
			?>
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
