<?php
namespace App\Service;

use App\DTO\PostDto;
use App\Entity\Post;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpClient\HttpClient;
use Throwable;

class File
{
    public function __construct(
        #[Autowire(param: 'kernel.project_dir')] private string $projectDir,
        private Filesystem $filesystem,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        #[Autowire(env: 'GRPC_GATEWAY_MS')] private string $grpcGatewayMs,
        #[Autowire(env: 'DEFAULT_URI')] private string $defaultUri
    ){}

    public function uploadPostPreview(Post $post, UploadedFile $preview, string $oldPreview = "")
    {
        $this->logger->info('Preview load to blog_ms', ['post_id' => $post->getId()]);
        try {
            $filename = hash('sha256', $preview->getClientOriginalName() . time()) . '.' . $preview->getClientOriginalExtension();
            $filePreview = fopen($preview->getPathname(), 'r');
            $client = HttpClient::create();
            $response = $client->request('POST', 'http://' . $this->grpcGatewayMs . '/v1/files/upload', [
                'headers' => [
                    'Content-Type' => 'application/octet-stream',
                    'X-Filename' => $filename,
                    'X-Old-Preview' => $oldPreview
                ],
                'body' => $filePreview
            ]);
            $response = $response->toArray();
            $filepath = $response['Filepath'];
            if ($filepath == "") {
                throw new \Exception("Error S3", 1);
            }
            $this->logger->info('Post Preview upload to s3', ['post_id' => $post->getId(), 'preview' => $filepath]);
        } catch (Throwable $e) {
            $this->logger->info('Post Preview Upload To S3 Error', ['errorMessage' => $e->getMessage()]);
            $postsFilepath = $this->projectDir . '/public/images/posts';
            $postFilepath = $postsFilepath . '/' . $post->getId() . '/preview';
            if ($this->filesystem->exists($postFilepath) == false) {
                $this->filesystem->mkdir($postFilepath, 0755);
            }
            $preview->move($postFilepath, $preview->getClientOriginalName());
            $filepath = $this->defaultUri . '/images/posts/' . $post->getId() . '/preview/' . $preview->getClientOriginalName();
            $this->logger->info('Post Preview upload to blog_ms', ['post_id' => $post->getId()]);
        } finally {
            $post->setPreview($filepath);
            $this->entityManager->persist($post);
            $this->entityManager->flush();
        }
    }

}