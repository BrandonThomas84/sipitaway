<?php /* FILEVERSION: v1.0.1b */ ?>
<?php

?>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>Sip It Away!</title>
  <meta name="description" content="Now your favorite Javita coffee products available online!">
  <meta name="Author" content="Brandon Thomas">
  <!-- © 2013 by Perspektive Designs -->

  <!-- START STYLE -->
  <link rel="stylesheet" href="style/bootstrap.css">
  <link rel="stylesheet" href="style/sipitaway.responsive.css">
  <link rel="stylesheet" href="style/sipitaway.style.css">
  <!-- bxSlider CSS file -->
  <link rel="stylesheet" href="style/bxslider.style.css">
  <!-- END STYLE -->

  <!-- START HEAD JS -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <!-- bxSlider Javascript file -->
  <script src="js/bxslider.js"></script>

  <!-- END HEAD JS -->
</head>

<body>
	<header>
		<div class="navbar navbar-inverse navbar-static-top main-nav">
		    <div class="navbar-header">
			  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	          </button>
		      <a class="navbar-brand" href="<?php echo $_SERVER['PHP_SELF']; ?> ">Sip it Away!</a>
		    </div>
		    <div class="navbar-collapse collapse" style="margin-left: 40px;">
		      <ul class="nav navbar-nav">
		        <li><a href="#">Home</a></li>
				<li class="dropdown">
		          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Drop Down Menu<b class="caret"></b></a>
		          <ul class="dropdown-menu">
		            <li class="dropdown-header"><strong>Dropdown Title</strong></li>
		            <li class="divider"></li>
		            <li><a href="#" title="Image 1">Link 1</a></li>
		            <li><a href="#" title="Image 2">Link 2</a></li>
		          </ul>
		        </li>
		      </ul>
		    </div>
		</div>
	</header>

	<div class="main-body">
		<div class="slider1 bxslider">
		  <div class="slide"><img src="http://placehold.it/350x150&text=FooBar1" title="This is an image!"></div>
		  <div class="slide"><img src="http://placehold.it/350x150&text=FooBar2" title="This is an image!"></div>
		  <div class="slide"><img src="http://placehold.it/350x150&text=FooBar3" title="This is an image!"></div>
		  <div class="slide"><img src="http://placehold.it/350x150&text=FooBar4" title="This is an image!"></div>
		  <div class="slide"><img src="http://placehold.it/350x150&text=FooBar5" title="This is an image!"></div>
		  <div class="slide"><img src="http://placehold.it/350x150&text=FooBar6" title="This is an image!"></div>
		  <div class="slide"><img src="http://placehold.it/350x150&text=FooBar7" title="This is an image!"></div>
		  <div class="slide"><img src="http://placehold.it/350x150&text=FooBar8" title="This is an image!"></div>
		  <div class="slide"><img src="http://placehold.it/350x150&text=FooBar9" title="This is an image!"></div>
		</div>
		<h1>Some Page Title</h1>
		<p>Some Content Here</p>
	</div>

	<footer>
	</footer>
</body>
<script>
	// Options http://bxslider.com/options
	$(document).ready(function(){
	  $('.bxslider').bxSlider({
	  		slideSelector: 'div.slide',
		    slideMargin: 0,
		    captions: true,
		    startSlide: 1,
		    speed: 700,
		    auto: true,
		    pause: 5000,
		    autoHover: true,
		    slideWidth: 1000,
		    randomStart: true
  		});
	});
</script>
</html>