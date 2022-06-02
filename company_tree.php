<?php

class Travel
{
    protected $id;
    protected $employeeName;
    protected $departure;
    protected $destination;
    protected $price;
    protected $companyId;
    protected $createdAt;

    function __construct($id, $employeeName, $departure, $destination, $price, $companyId, $createdAt)
    {
        $this->id = $id;
        $this->employeeName = $employeeName;
        $this->departure = $departure;
        $this->destination = $destination;
        $this->price = $price;
        $this->companyId = $companyId;
        $this->createdAt = $createdAt;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getEmployeeName() {
        return $this->employeeName;
    }

    public function setEmployeeName($employeeName) {
        $this->employeeName = $employeeName;
    }

    public function getDeparture() {
        return $this->departure;
    }

    public function setDeparture($departure) {
        $this->departure = $departure;
    }

    public function getDestination() {
        return $this->destination;
    }

    public function setDestination($destination) {
        $this->$destination = $destination;
    }

    public function getPrice() {
        return $this->price;
    }

    public function setPrice($price) {
        $this->$price = $price;
    }

    public function getCompanyId() {
        return $this->companyId;
    }

    public function setCompanyId($companyId) {
        $this->$companyId = $companyId;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt) {
        $this->$createdAt = $createdAt;
    }
}

class Company
{
    protected $id;
    protected $name;
    protected $parentId;
    protected $cost;
    protected $children;
    protected $createdAt;

    function __construct($id, $name, $parentId, $createdAt)
    {
        $this->id = $id;
        $this->name = $name;
        $this->parentId = $parentId;
        $this->createdAt = $createdAt;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->$name = $name;
    }

    public function getParentId() {
        return $this->parentId;
    }

    public function setParentId($parentId) {
        $this->$parentId = $parentId;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt) {
        $this->$createdAt = $createdAt;
    }
}

class TestScript
{
    private $baseUrl = "https://5f27781bf5d27e001612e057.mockapi.io/webprovise";
    private $travelApiEndpoint = "/travels";
    private $companyApiEndpoint = "/companies";

    public function fetchData($serviceUrl) {
        $curl = curl_init($serviceUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function execute()
    {
        $start = microtime(true);
        $companies = $this->fetchData($this->baseUrl.$this->companyApiEndpoint);
        $travels = $this->fetchData($this->baseUrl.$this->travelApiEndpoint);
        $result = [];
        foreach ($companies as $key => $company) {
            $dataItem = [
                'id' => $company->id,
                'name' => $company->name,
                'cost' => 0,
                'parentId' =>  $company->parentId,
                'children' => [],
            ];
            foreach ($travels as $travelKey => $travel) {
                if ($travel->companyId === $company->id) {
                    $dataItem['cost'] += $travel->price;
                    unset($travels[$travelKey]);
                }
            }
            $exist = false;
            foreach($result as $rsKey => $rs) {
                if($rs['id'] === $company->parentId) {
                    unset($dataItem['parentId']);
                    $result[$rsKey]['children'][] = $dataItem;
                    $exist = true;
                }else {
                    $result[$rsKey]['children'] = $this->createTree($rs['children'], $dataItem);
                    $exist = count($result[$rsKey]['children']) > 0;
                }
                if($exist) break;
            }
            unset($dataItem['parentId']);
            if(!$exist) $result[] = $dataItem ;
        }
        echo json_encode($result);
        echo 'Total time: ' .  (microtime(true) - $start);
    }

    function createTree($list, $child){
        foreach($list as $key => $item) {
            if($child['parentId'] === $item['id']) {
                $list[$key]['children'][] = $child;
            }else {
                $list[$key]['children'] = $this->createTree($item['children'], $child);
            }
            unset($list[$key]['parentId']);
        }
        return $list;
    }
}

(new TestScript())->execute();
