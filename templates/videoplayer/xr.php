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

$uid = uniqid('sexvideo_');
?>
<video id='<?php echo $uid; ?>' class='video-js vjs-default-skin vjs-2-1 vjs-big-play-centered' style='width: 100%; height: 100%;' controls poster='<?php echo $posters; ?>'>
</video>
<script language='javascript'>
	$(window).on('load', function() {
      var player = videojs('<?php echo $uid; ?>', {
              html5: {
                  vhs: {
                   overrideNative: !videojs.browser.IS_SAFARI
                  },
              nativeAudioTracks: false,
              nativeVideoTracks: false
              }});
      player.src({ src: '<?php echo $vurl; ?>', type: 'application/x-mpegURL'});
      player.xr();
   });
</script>
