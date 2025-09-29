<?php

namespace JakeAndCo;

if (!defined('ABSPATH')) {
  exit;
}

class Menu
{
  public static function build_menu_tree($menu_id, $extra_pages = [])
  {
    return new \JakeAndCo\MenuTree\MenuTree($menu_id, $extra_pages);
  }

  public static function get_menu_item($menu_id, $page_id)
  {
    $menu_tree = self::build_menu_tree($menu_id);

    return $menu_tree->get_menu_item($page_id);
  }

  public static function find_highest_parent($menu_id, $page_id)
  {
    return self::get_menu_item($menu_id, $page_id)->get_highest_ancestor();
  }

  public static function get_breadcrumbs($menu_id, $page_id)
  {
    $ancestors = self::get_menu_item($menu_id, $page_id)->get_ancestors();
    return array_reverse($ancestors);
  }
}

new Menu();
