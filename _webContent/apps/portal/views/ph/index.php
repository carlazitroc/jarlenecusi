
<section class="main">
<div class="container">
<div class="page-header">
<h1><?php echo $section?></h1>
</div>

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