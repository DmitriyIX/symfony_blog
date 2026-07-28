<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PostDto
{
    public function __construct(
        public ?string $id
    ) {
    }
}