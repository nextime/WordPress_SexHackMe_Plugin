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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// This is a dirty trick to hide the Visibility section in the publish metabox.
echo '<style>div#visibility.misc-pub-section.misc-pub-visibility{display:none}</style>';
?>
   <p>
      <h4>Video description</h4>
      <textarea style="width:100%" id="video_description" name="video_description"><?php  echo esc_attr( $video->description ); ?></textarea>
   </p>
   <p>
      <h4>Privacy:</h4>
      <p>
         <label> * Show video in public gallery?</label>
         <input type='radio' name='video_visible' value='Y'>Yes</input>
         <input type='radio' name='video_visible' value='N'>No</input>
      </p>
      <p>
         <label> * Show video in profile gallery?</label>
         <input type='radio' name='video_private' value='N'>Yes</input>
         <input type='radio' name='video_private' value='Y'>No</input>
      </p>
   </p>
   <p>
      <h4>Preview</h4>
      <p>
         <label> * Thumbnail (URI,PATH or thumbail ID):</label>
         <input type='text' nane="video_thumbnail" value='<?php  echo esc_attr( $video->thumbnail ); ?>' > (Override featured image)</input>
      </p>
      <p>
         <label> * Animated GIF (URI or PATH):</label>
         <input type='text' nane="video_gif" value='<?php  echo esc_attr( $video->gif ); ?>'  />
      </p>
      <p>
         <label> * Video preview/teaser (max 1 min)</label>
         <input type='text' nane="video_preview" value='<?php  echo esc_attr( $video->preview ); ?>' />
      <p>
   </p>

   <p>
      <h4>Virtual Reality</h4>
      <p>
         <label> * VR Video?:</label>
         <input type='radio' name='video_type' value='VR'>Yes</input>
         <input type='radio' name='video_type' value='FLAT'>No</input>
      </p>
      <p>
         <label> * VR Projection</label>
         <select name='video_vr_projection'>
            <option value='VR180_LR'>Equirectangular 180 LR</option>
            <option value='VR360_LR'>Equirectangular 360 LR</option>
         </select>
      </p>
   </p>
   <p>
      <h4>Price:</h4>
      <label>USD:</label>
      <input type='text' nane="video_price" value='<?php  echo esc_attr( $video->price ); ?>' />
   </p>
   <p>
		<?php 
			$vaccess=array('public','members','premium');
		?>
		<h4>Video media files</h4>
   	<div id="sh_admin_tabs">
     		<ul class="category-tabs">
				<?php
				foreach($vaccess as $vt) { ?>
          		<li><a href="#t_<?php echo $vt; ?>"><?php echo ucfirst($vt);?></a></li>
				<?php } ?>
     	 	</ul>
     		<br class="clear" />
			<?php
			foreach($vaccess as $vt) { 
					$vthls = 'hls_'.$vt;
					$vtdown = 'download_'.$vt;
					$vtsize = 'size_'.$vt;
					$vtresolution = 'resolution_'.$vt;
					$vtformat = 'format_'.$vt;
					$vtcodec = 'codec_'.$vt;
					$vtacodec = 'acodec_'.$vt;
					$vtduration = 'duration_'.$vt;
				?>
     			<div class="hidden" id="t_<?php echo $vt; ?>">
					<h4> <?php echo ucfirst($vt); ?> files</h4>
           		<p>
                   <label> * Download (URI or PATH):</label>
                   <input type='text' nane="video_download_<?php echo $vt;?>" value='<?php  echo esc_attr( $video->__get($vtdown) ); ?>'  />
					</p>
					</p>
						 <label> * HLS playlist (URI or PATH):</label>
						 <input type='text' nane="video_hls_<?php echo $vt;?>" value='<?php  echo esc_attr( $video->__get($vthls ) ); ?>'  />
					</p>
               </p>
                   <label> * Duration:</label>
                   <input type='text' nane="video_duration_<?php echo $vt;?>" value='<?php  echo esc_attr( $video->__get($vtduration ) ); ?>'  />
               </p>
               </p>
                   <label> * File size:</label>
                   <input type='text' nane="video_size_<?php echo $vt;?>" value='<?php  echo esc_attr( $video->__get($vtsize ) ); ?>'  />
               </p>
               </p>
                   <label> * Resolution:</label>
                   <input type='text' nane="video_resolution_<?php echo $vt;?>" value='<?php  echo esc_attr( $video->__get($vtresolution ) ); ?>'  />
               </p>
               </p>
                   <label> * Format:</label>
                   <input type='text' nane="video_format_<?php echo $vt;?>" value='<?php  echo esc_attr( $video->__get($vtformat ) ); ?>'  />
               </p>
               </p>
                   <label> * Codec:</label>
                   <input type='text' nane="video_codec_<?php echo $vt;?>" value='<?php  echo esc_attr( $video->__get($vtcodec ) ); ?>'  />
               </p>
               </p>
                   <label> * Audio codec:</label>
                   <input type='text' nane="video_acodec_<?php echo $vt;?>" value='<?php  echo esc_attr( $video->__get($vtacodec ) ); ?>'  />
               </p>

     			</div>
		<?php } ?>
	</div>
</p>
