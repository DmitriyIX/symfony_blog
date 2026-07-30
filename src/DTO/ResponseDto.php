<?php
namespace App\DTO;

class ResponseDto
{
    public function __construct(
        public bool $success,
        public string $message,
        public mixed $data,
        public mixed $error
    ){}

    public function getResponse(): array
    {
        $response = ['success' => $this->success, 'message' => $this->message];
        if ($this->data !== null) {
            $response['data'] = $this->data;
        }
        if ($this->error !== null) {
            $response['error'] = $this->error;
        }
        return $response;
    }
}
?>