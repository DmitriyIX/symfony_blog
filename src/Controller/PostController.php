<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use App\DTO\PostDto;

class PostController extends AbstractController
{
    #[Route('/test')]
    public function test(
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\d+$/'])] string $t = '345'
    ): Response
    {

        return new Response("<div>test</div><div>Param: " . $t . "</div>");
    }

    #[Route('/posts', methods: 'GET')]
    public function list(
        #[MapQueryString(mapWhenEmpty: true)] PostDto $postDto,
        Request $request
    ): JsonResponse
    {
        dd($request->getPreferredLanguage(['ru', 'fr']));
        dd($postDto);
        return new JsonResponse(['data' => ['id' => $postDto->id]]);
    }

    #[Route('/posts/{id}', methods: 'GET')]
    public function show(
        int $id
    ): Response
    {
        if ($id == 1) {
            return $this->render('post.html.twig', ['id' => 1]);
        }
        throw $this->createNotFoundException('Post not found');
    }
}