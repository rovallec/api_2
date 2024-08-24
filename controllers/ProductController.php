<?php
include_once 'models/Product.php';

class ProductController {
    private $db;
    private $product;

    public function __construct($db) {
        $this->db = $db;
        $this->product = new Product($db);
    }

    public function getAll() {
        $stmt = $this->product->read();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::send(200, $products);
    }

    public function getSingle($id) {
        $this->product->id = $id;
        $stmt = $this->product->read_single();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product) {
            Response::send(200, $product);
        } else {
            Response::send(404, ['message' => 'Product not found']);
        }
    }

    public function create($data) {
        $this->product->name = $data['name'];
        $this->product->description = $data['description'];
        $this->product->price = $data['price'];
        if ($this->product->create()) {
            Response::send(201, ['message' => 'Product created']);
        } else {
            Response::send(500, ['message' => 'Product not created']);
        }
    }

    public function update($id, $data) {
        $this->product->id = $id;
        $this->product->name = $data['name'];
        $this->product->description = $data['description'];
        $this->product->price = $data['price'];
        if ($this->product->update()) {
            Response::send(200, ['message' => 'Product updated']);
        } else {
            Response::send(500, ['message' => 'Product not updated']);
        }
    }

    public function delete($id) {
        $this->product->id = $id;
        if ($this->product->delete()) {
            Response::send(200, ['message' => 'Product deleted']);
        } else {
            Response::send(500, ['message' => 'Product not deleted']);
        }
    }
}
?>
