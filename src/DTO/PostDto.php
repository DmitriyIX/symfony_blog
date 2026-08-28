<?php
namespace App\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class PostDto
{
    public function __construct(
        public ?string $title = null,
        public ?string $content = null,
        public ?UploadedFile $preview = null,
        public ?int $status = 1
    ) {
    }
}