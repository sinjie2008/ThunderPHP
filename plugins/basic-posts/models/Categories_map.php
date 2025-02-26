<?php

namespace BasicPosts;
use \Model\Model;

defined('ROOT') or die("Direct script access denied");

/**
 * Categories_map class
 */
class Categories_map extends Model
{

	protected $table = 'posts_categories_map';
	public $primary_key = 'id';

	protected $allowedColumns = [
		'post_id',
		'category_id',
		'disabled',
	];
	
	protected $allowedUpdateColumns = [
		'post_id',
		'category_id',
		'disabled',
	];


	public function disable_all(int $post_id)
	{
 		
 		$this->query("update $this->table set disabled = 1 where post_id = :post_id",['post_id'=>$post_id]);
	}

	public function save_new(int $post_id, array $category_ids)
	{
 		foreach ($category_ids as $id) {

 			if($check = $this->first(['post_id'=>$post_id,'category_id'=>$id]))
 			{
 				$this->update($check->id,['disabled'=>0]);
 			}else{
 				$this->insert([
 					'post_id'=>$post_id,
 					'category_id'=>$id,
 					'disabled'=>0,
 				]);
 			}
 		}
	}


	public function get_category_ids(int $post_id):array
	{

		if($rows = $this->where(['post_id'=>$post_id,'disabled'=>0]))
			return array_column($rows, 'category_id');

		return [];
	}
	
	public function get_category_rows(int $post_id):array
	{
		$cats_table = get_value()['tables']['categories_table'];

		if($rows = $this->query("select * from $cats_table where disabled = 0 && id in (select category_id from $this->table where post_id =:post_id && disabled = 0)",['post_id'=>$post_id]))
			return $rows;
		
		return [];
	}
	
	
}