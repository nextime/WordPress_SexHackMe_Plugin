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

function sh_save_video($video)
{
   return SH_Query::save_Video($video);
}

function sh_delete_video($v)
{
   if(is_integer($v) && (intval($v)) > 0) return SH_Query::delete_Video($v);
   else if(is_object($v)) return SH_Query::delete_Video($v->id);
   return false;
}

function sh_delete_video_from_post($p)
{
   if(is_int($p) && $p > 0) return SH_Query::delete_Video($p, 'post');
   else if(is_object($p)) return SH_Query::delete_Video($p->ID, 'post');
   return false;
}

function sh_delete_video_from_product($p)
{
   if(is_int($p) and $p > 0) return SH_Query::delete_Video($p, 'product');
   else if(is_object($p)) return SH_Query::delete_Video($p->get_id(), 'product');
   return false;
}


function sh_get_videos($vcat=false)
{
   return SH_Query::get_Videos($vcat);
}

function sh_get_video($id)
{
   return SH_Query::get_Video($id);
}

function sh_get_video_from_post($p)
{
   if(is_int($p) && $p > 0) return SH_Query::get_Video($p, 'post');
   else if(is_object($p)) return SH_Query::get_Video($p->ID, 'post');
   return false;
}

function sh_get_video_from_product($p)
{
   if(is_int($p) and $p > 0) return SH_Query::get_Video($p, 'product');
   else if(is_object($p)) return SH_Query::get_Video($p->get_id(), 'product');
   return false;
}


?>
