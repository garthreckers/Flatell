<?php
namespace Spinion;

abstract class RestController extends Controller
{
    /**
     * @var array $request Will get populated with
     *  the JSON request body when class is initialized
    */
    protected $request;

    /**
     * @var array $response An array of the reponse data
     *  that will get JSON encoded and sent to the client
    */
    protected $response;

    public function __construct($params = array())
    {
        parent::__construct($params);

        $this->response = array();

        $raw = file_get_contents('php://input');
        $this->request = isset($raw) ? json_decode($raw) : array();
    }

    /**
     * Adds a key value to the response array that will get
     *  JSON encoded and echo'd when renderJson is called
     *
     * @param string $key The key for the JSON object
     * @param mixed $value The value for the JSON object
     *
     * @return void 
    */
    public function addResponse($key, $value)
    {
        $this->response[$key] = $value;
    }

    /**
     * When called, this method will add in the JSON content type
     *  header and json_encode the response code
    */
    public function renderJson()
    {
        $output = $this->response;

        header('Content-Type: application/json');

        echo json_encode($output);
    }

    protected function showPage()
    {
        return false;
    }
}
