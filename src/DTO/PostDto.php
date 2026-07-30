<?php
namespace App\DTO;

class PostDto
{
    public function __construct(
        public string $title,
        public ?string $content,
        public ?string $preview,
        public ?int $status = 1
    ) {
    }
}