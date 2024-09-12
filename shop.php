<?php
include "config/connect.php";

$sort = '';
$keyword = '';
$min_price = 0;
$max_price = 1000;
$brands_target = [];
$categories_target = [];

$base_query = 'SELECT * FROM products WHERE 1=1';
$params = [];

if (isset($_GET['categories'])){
   $categories_target = explode('_', $_GET['categories']);
   $categories_placeholders = implode(',', array_fill(0, count($categories_target), '?'));
   $base_query .= " AND category_slug IN ($categories_placeholders)";
   $params = array_merge($params, $categories_target);
}
if (isset($_GET['brands'])){
   $brands_target = explode('_', $_GET['brands']);
   $brands_placeholders = implode(',', array_fill(0, count($brands_target), '?'));
   $base_query .= " AND brand_slug IN ($brands_placeholders)";
   $params = array_merge($params, $brands_target);
}

if (isset($_GET['search'])){
   $keyword = $_GET['search'];
   $base_query .= ' AND title LIKE ?';
   $params[] = '%'.$keyword.'%';
}

if (isset($_GET['min_price']) && isset($_GET['max_price'])){
   $min_price = $_GET['min_price'];
   $max_price = $_GET['max_price'];
   $base_query .= ' AND price BETWEEN ? AND ?';
   $params[] = $min_price;
   $params[] = $max_price;
}

if (isset($_GET['sort'])){
   $sort = $_GET['sort'];
   if ($sort == 'latest'){
      $base_query .= ' ORDER BY id DESC';
   } elseif ($sort == 'price_low'){
      $base_query .= ' ORDER BY price ASC';
   } elseif ($sort == 'price_high'){
      $base_query .= ' ORDER BY price DESC';
   }
}

$products = $database->prepare($base_query);
if($products->execute($params)){
   $products = $products->fetchAll(PDO::FETCH_OBJ);
}

$brands = $database->prepare('SELECT * FROM brands');
if($brands->execute()){
   $brands = $brands->fetchAll(PDO::FETCH_OBJ);
}

$categories = $database->prepare('SELECT * FROM categories');
if($categories->execute()){
   $categories = $categories->fetchAll(PDO::FETCH_OBJ);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="assets/plugins/bootstrap/v5.3/css/bootstrap.min.css">
   <link rel="stylesheet" href="assets/plugins/font-awesome/v6.5/css/all.min.css">
   <link rel="stylesheet" href="assets/plugins/ion.rangeSlider/ion.rangeSlider.min.css">
   <link rel="stylesheet" href="assets/css/shop.css">
   <link rel="stylesheet" href="assets/css/slick.css">
   <link rel="stylesheet" href="assets/css/style.css">
   <link rel="stylesheet" href="assets/css/slick-theme.css">
   <title>Shop</title>
</head>
<body>
<body data-instant-intensity="mousedown">
   <div class="bg-light top-header">        
      <div class="container">
         <div class="row align-items-center py-3 d-none d-lg-flex justify-content-between">
            <div class="col-lg-4 logo">
               <a href="shop.php" class="text-decoration-none">
                  <span class="h1 text-primary bg-dark px-2">ONLINE</span>
                  <span class="h1 text-dark px-2 ml-n1">SHOP</span>
               </a>
            </div>
            <div class="col-lg-6 col-6 text-left d-flex justify-content-end align-items-center">
               <a href="" class="nav-link text-dark">My Account</a>
               <form>
                  <div class="input-group">
                     <input type="text" name="search" id="search" placeholder="Search For Products" class="form-control" value="<?= $keyword ?>" autocomplete="off">
                     <button type="submit" class="input-group-text">
                        <i class="fa fa-search"></i>
                     </button>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
   <main>
      <section class="section-5 pt-3 pb-3 mb-3 bg-white">
         <div class="container">
            <div class="light-font">
               Home / Shop
            </div>
         </div>
      </section>
      <section class="section-6 pt-5">
         <div class="container">
            <div class="row">
               <div class="col-md-3 sidebar">
                  <div class="sub-title">
                     <h2>Categories</h2>
                  </div>
                  <div class="card">
                     <div class="card-body mx-3">
                        <form name="categoryForm" id="categoryForm"><?php
                        foreach($categories as $category): ?>
                           <div class="form-check mb-2">
                              <input <?= in_array($category->slug, $categories_target) ? 'checked' : '' ?> type="checkbox" class="form-check-input categories" name="category" value="<?= $category->slug ?>" id="category-<?= $category->slug ?>" onclick="updateURL('categories' ,this.checked)">
                              <label class="form-check-label" for="category-<?= $category->slug ?>">
                                 <?= $category->name ?>
                              </label>
                           </div><?php
                        endforeach ?>
                        </form>
                     </div>
                  </div>
                  <div class="sub-title mt-5">
                     <h2>Brand</h3>
                  </div>
                  <div class="card">
                     <div class="card-body mx-3">
                        <form name="brandForm" id="brandForm"><?php
                        foreach($brands as $brand): ?>
                           <div class="form-check mb-2">
                              <input <?= in_array($brand->slug, $brands_target) ? 'checked' : '' ?> type="checkbox" class="form-check-input brands" name="brand" value="<?= $brand->slug ?>" id="brand-<?= $brand->slug ?>" onclick="updateURL('brands' ,this.checked)">
                              <label class="form-check-label" for="brand-<?= $brand->slug ?>">
                                 <?= $brand->name ?>
                              </label>
                           </div><?php
                        endforeach ?>
                        </form>
                     </div>
                  </div>
                  <div class="sub-title mt-5">
                     <h2>Price</h3>
                  </div>
                  <div class="card">
                     <div class="card-body">
                        <input type="text" class="js-range-slider" name="my_range" value=""/>
                     </div>
                  </div>
               </div>
               <div class="col-md-9">
                  <div class="row pb-3">
                     <div class="col-12 pb-1 mt-4">
                        <div class="d-flex align-items-center justify-content-end mb-4">
                           <div class="ml-2">
                           <form id="sortForm">
                              <select name="sort" id="sort" class="form-control" onchange="sortForm()">
                                 <option value="" disabled selected>Filter by:</option>
                                 <option value="latest" <?= $sort == 'latest' ? 'selected' : '' ?>>Latest</option>
                                 <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?> >Price High</option>
                                 <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?> >Price Low</option>
                              </select>
                           </form>
                           </div>
                        </div>
                     </div><?php

                     foreach($products as $product): ?>
                     <div class="col-md-4">
                        <div class="card product-card">
                           <div class="product-image position-relative">
                              <a href="" class="product-img">
                                 <img class="card-img-top" src="assets/imgs<?= $product->thumbnail ?>">
                              </a>
                              <div class="product-action">
                                 <a class="btn btn-dark">
                                    Out Of Stock
                                 </a>
                              </div>
                           </div>
                           <div class="card-body text-center mt-3">
                              <a class="h6 link" href=""><?= $product->title ?></a>
                              <div class="price mt-2">
                                 <span class="h5"><strong>$<?= $product->price ?></strong></span>
                                 <span class="h6 text-underline"><del><?= $product->compare_price ?></del></span>
                              </div>
                           </div>
                        </div>
                     </div><?php 
                     endforeach ?>
                  </div>
               </div>
            </div>
         </div>
      </section>
   </main>
   <script src="assets/plugins/bootstrap/v5.3/js/bootstrap.bundle.min.js"></script>
   <script src="assets/plugins/font-awesome/v6.5/js/all.min.js"></script>
   <script src="assets/plugins/jquery/v3.7/jquery.min.js"></script>
   <script src="assets/plugins/ion.rangeSlider/ion.rangeSlider.min.js"></script>
   <script>
   function sortForm() {
      updateURL();
   }

   function updateURL(query_key=null, is_checked=null) {
      const url = new URL(window.location.href);

      var query_val = '';
      $('.'+query_key).each(function(){
         if($(this).is(':checked') == true){
            query_val += $(this).val()+'_';
         }
      });

      if (query_val.endsWith('_')) {
         query_val = query_val.slice(0, -1);
      }

      if (is_checked) {
         url.searchParams.set(query_key, query_val);
      } else {
         if(query_val.length > 0){
            url.searchParams.set(query_key, query_val);
         } else {
            url.searchParams.delete(query_key);
         }
      }

      const sort = document.getElementById('sort').value;
      if (sort) {
         url.searchParams.set('sort', sort);
      } else {
         url.searchParams.delete('sort');
      }

      window.history.replaceState({}, '', url);
      window.location.reload();
   }

   rangeSlider = $(".js-range-slider").ionRangeSlider({
      type: "double",
      min: 0,
      max: 1000,
      from: <?= $min_price ?>,
      to: <?= $max_price ?>,
      step: 100,
      skin: "round",
      max_postfix: "+",
      prefix: "$",
      onFinish: function(data) {
         const url = new URL(window.location.href);
         url.searchParams.set('min_price', data.from);
         url.searchParams.set('max_price', data.to);
         window.history.replaceState({}, '', url);
         window.location.reload();
      }
   });

   var slider = $(".js-range-slider").data("ionRangeSlider");
   </script>
   </body>
</html>