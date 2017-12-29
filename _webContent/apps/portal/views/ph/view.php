
<section class="main">
<div class="container">
<div class="post">
<div class="post-caption">
<h1><?php echo $section ?></h1>
<h2><?php echo $post['title'] ?></h2>
</div>

<div class="post-content">
<?php echo $post['content'] ?>

<div class="clearfix"></div>
<?php if(isset($post['tags']) && is_array($post['tags'])){  ?>
<ul class="tags">
<?php foreach($post['tags'] as $idx => $tag_row){?>
	<li><a href="<?php echo site_url($section.'/tag/'.$tag_row['_mapping'])?>"><?php echo $tag_row['title']?></a></li>
	<?php 
}

?>
</ul>
</div>
<div class="clearfix"></div>
<?php } ?>


</div>
</div>
</section>