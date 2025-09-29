<?php

namespace JakeAndCo\MenuTree;

use LimeRockTheme\Util;

if (!defined('ABSPATH')) {
  exit;
}


class MenuTree
{
  public $post_map = [];
  public $menu_map = [];
  public $menu_tree = [];

  public function __construct($menu_id, $page_id, $extra_pages = [])
  {
    $nav_menu_items = wp_get_nav_menu_items($menu_id);

    $all_nav_menu_items = array_merge($nav_menu_items, $extra_pages);

    _wp_menu_item_classes_by_context($all_nav_menu_items);

    foreach ($all_nav_menu_items as $menu_item) {

      $current_id = strval($menu_item->ID);
      $current_object_id = strval($menu_item->object_id);
      $parent_id = strval($menu_item->menu_item_parent);


      $current_node = Util::array_value($this->menu_map, $current_id) ?: new \JakeAndCo\MenuTree\MenuTreeNode($this);
      $parent_node = Util::array_value($this->menu_map, $parent_id) ?: new \JakeAndCo\MenuTree\MenuTreeNode($this);

      $this->menu_map[$current_id] = $current_node;
      $this->post_map[$current_object_id] = $current_node;

      $this->menu_map[$parent_id] = $parent_node;

      $current_node->set_menu_item($menu_item);
      $current_node->set_parent($parent_node);

      $parent_node->add_child($current_node);
    }

    $this->menu_tree = $this->menu_map['0']->children;

    foreach ($this->menu_tree as $top_level_item) {
      $top_level_item->set_parent(null);
    }
  }

  public function get_menu()
  {
    return $this->menu_tree;
  }
  public function get_menu_item($menu_item_id)
  {
    return Util::array_value($this->menu_map, strval($menu_item_id)) ?: null;
  }

  public function get_post_menu_item($post_id)
  {
    return Util::array_value($this->post_map, strval($post_id)) ?: null;
  }
}
class MenuTreeNode
{
  public $id = 0;
  public $parent_id = null;
  public $menu_item = null;
  public $children = [];

  private $menu_tree = null;
  private $parent = null;

  public function __construct($menu_tree, $menu_item = null)
  {
    $this->menu_tree = $menu_tree;
    $this->menu_item = $menu_item;
  }

  public function get_children()
  {
    return $this->children;
  }
  public function add_child($menu_item_node)
  {
    if (
      empty(array_filter($this->children, function ($child) use ($menu_item_node) {
        return $child->id == $menu_item_node->id;
      }))
    ) {
      $this->children[] = $menu_item_node;
    }
  }

  public function get_parent()
  {
    return $this->parent;
  }

  public function set_parent($menu_item_node)
  {
    if ($menu_item_node) {
      $this->parent_id = strval($menu_item_node->id);
    } else {
      $this->parent_id = null;
    }

    $this->parent = $menu_item_node;
    return $this;
  }

  public function get_ancestors()
  {
    $current_menu_node = $this->get_parent();
    $ancestors = [];

    while (!empty($current_menu_node) && $current_menu_node->id != '0') {
      $ancestors[] = $current_menu_node;
      $current_menu_node = $current_menu_node->get_parent();
    }

    return $ancestors;
  }

  public function get_highest_ancestor()
  {
    $ancestors = $this->get_ancestors();
    return end($ancestors);
  }

  public function get_menu_item()
  {
    return $this->menu_item;
  }
  public function set_menu_item($menu_item)
  {
    $this->id = strval($menu_item->ID);
    $this->menu_item = $menu_item;
    return $this;
  }

  public function get_menu()
  {
    return $this->menu_tree;
  }
}
