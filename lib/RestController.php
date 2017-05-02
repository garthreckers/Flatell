<?php
namespace Spinion;

abstract class RestController extends Controller
{
    protected $request;
    protected $response;

    protected function showPage()
    {
        return false;
    }

    public function __construct()
    {
        parent::__construct();

        $this->response = array();

        $raw = file_get_contents('php://input');
        $this->request = json_decode($raw);
    }

    public function render()
    {
        $output = array();

        $output['data'] = $this->response;

        echo json_encode($output);
    }
}
