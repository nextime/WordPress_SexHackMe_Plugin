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

$uid = uniqid('sexhls_');
?>
   <video id="<?php echo $uid; ?>" style="width: 100%; height: 100%;" controls poster="<?php echo $posters; ?>"></video>
<script language="javascript">
	$(window).on('load', function() {
            SexHLSPlayer('<?php echo $vurl; ?>', '<?php echo $uid; ?>');
            $('#<?php echo $uid; ?>').on('click', function(){this.paused?this.play():this.pause();});
            Mousetrap(document.getElementById('<?php echo $uid; ?>')).bind('space', function(e, combo) { SexHLSplayPause('<?php echo $uid; ?>'); });
            Mousetrap(document.getElementById('<?php echo $uid; ?>')).bind('up', function(e, combo) { SexHLSvolumeUp('<?php echo $uid; ?>'); });
            Mousetrap(document.getElementById('<?php echo $uid; ?>')).bind('down', function(e, combo) { SexHLSvolumeDown('<?php echo $uid; ?>'); });
            Mousetrap(document.getElementById('<?php echo $uid; ?>')).bind('right', function(e, combo) { SexHLSseekRight('<?php echo $uid; ?>'); });
            Mousetrap(document.getElementById('<?php echo $uid; ?>')).bind('left', function(e, combo) { SexHLSseekLeft('<?php echo $uid; ?>'); });
            Mousetrap(document.getElementById('<?php echo $uid; ?>')).bind('f', function(e, combo) { SexHLSvidFullscreen('<?php echo $uid; ?>'); });
         });
</script>
