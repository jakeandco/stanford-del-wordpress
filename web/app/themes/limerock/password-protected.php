<?php
use Timber\Timber;

$context = Timber::context([
  'post' => Timber::get_post(),
]);

Timber::render('password-protected.twig', $context);
