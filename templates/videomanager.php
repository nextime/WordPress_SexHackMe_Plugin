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
$videos = sh_get_my_videos();
?>
<h3>My Videos</h3>
<p><a href="?vedit=new"><button>+ Add New Video </button></a></p>
<?php
foreach($videos as $video)
{
?>
<p>
<table style="border: 1px solid white">
   <tr>
     <label><?php echo $video->get_title();?></label>
   </tr>
   <tr>
      <td>thumbnail</td>
      <td>others data</td>
      <td>
      <a href="?vedit=<?php echo $video->id; ?>"><button>Edit</button></a>
      </td>
   </tr>
</table>
</p>
   <?php
}
?>
