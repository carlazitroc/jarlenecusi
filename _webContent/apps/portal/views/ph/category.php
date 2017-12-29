
<section class="main">
<div class="container">
<?php if(!empty($current_category)){?>
<div class="page-header">
<h2><?php echo $current_category['title']?></h2>
</div>
<?php } ?>

<?php if(isset($posts['data']) && is_array($posts)){ ?>
<?php foreach($posts['data'] as $idx => $post){?>
<div class="media">
<div class="media-content">

<a href="<?php echo ($post['loc_url'])?>" class="media-heading">
<?php print $post['title']?>
</a>

</div>
</div>
<?php } ?>
<?php } ?>
</div>
</section>