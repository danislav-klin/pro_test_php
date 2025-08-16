<?php

namespace App\Controller;

use App\Model\ProductRepository;

class ProductController
{
    private $repository;

    public function __construct()
    {
        $this->repository = new ProductRepository();
    }

    public function list()
    {
        $filters = [
            'name' => $_GET['search_name'],
            'category_id' => $_GET['search_category']
        ];
        
        $products = $this->repository->findAll($filters);
        $categories = $this->repository->findAllCategories();
        
        $seo_data = [
            'title' => 'Наш Каталог Продуктов - ProShop',
            'description' => 'Управление продуктами'
        ];
        
        $this->render('product_list', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
            'seo' => $seo_data
        ]);

    }

    public function show($id)
    {
        $product = $this->repository->findById($id);

        if (!$product) {
            header("HTTP/1.0 404 Not Found");
            echo "<h1>404 - Продукт не найден (или метод findById не реализован)</h1>";
            exit;
        }

        $seo_data = [
            'title' => $product['name'] . ' - ' . $product['category_name'] . ' | ProShop',
            'description' => mb_substr(strip_tags($product['description']), 0, 150),
            'image' => $product['image_url'] ?? '/assets/default.png',
            'url' => 'http://localhost/index.php?action=show&id=' . $product['id']
        ];

        $this->render('product_detail', [
            'product' => $product,
            'seo' => $seo_data
        ]);
    }

    public function add()
    {
        $categories = $this->repository->findAllCategories();
        $this->render('add_product', ['categories' => $categories]);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => $_POST['price'],
                'category_id' => $_POST['category_id']
            ];

            $errors = [];

            if ($data['name'] === '') {
                $errors[] = 'Поле "Название" обязательно для заполнения.';
            }
            if ($data['price'] === '' || !is_numeric($data['price']) || $data['price'] <= 0) {
                $errors[] = 'Поле "Цена" должно быть положительным числом.';
            }
            if (empty($errors)) {
                $this->repository->create($data);
                header('Location: /index.php?action=list');
                exit;
            } else {
                $categories = $this->repository->findAllCategories();
                $this->render('add', [
                    'categories' => $categories,
                    'errors' => $errors,
                    'old' => $data
                ]);
                return;
            }
        }
    }

    public function edit($id)
    {
        $product = $this->repository->findById($id);
        $categories = $this->repository->findAllCategories();
        $this->render('edit_product', [
            'product' => $product,
            'categories' => $categories
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'category_id' => $_POST['category_id']
        ];

        $this->repository->update($id, $data);
        header('Location: /index.php?action=list');
        exit;
    }
    }
    
    public function delete($id)
    {
        if ($id) {
            $this->repository->deleteById($id);
        }
        header('Location: /index.php?action=list');
        exit();
    }
    
    public function import()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file']['tmp_name'];

            if (($handle = fopen($file, 'r')) !== false) {
                fgetcsv($handle, 1000, ',');

                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    if (count($row) < 5 || empty($row[0]) || empty($row[2])) {
                        continue;
                    }

                    $data = [
                        'name' => $row[0],
                        'description' => $row[1] ?? '',
                        'price' => $row[2],
                        'category_id' => $row[3] ?? null,
                        'image_url' => $row[4] ?? null,
                    ];

                    $this->repository->create($data);
                }

                fclose($handle);
            }
            header('Location: /index.php?action=list');
            exit();
        }

        $this->render('import_form');
    }

    public function export()
    {
        $filters = [
            'name' => $_GET['search_name'] ?? null,
            'category_id' => $_GET['search_category'] ?? null,
        ];

        $products = $this->repository->findAll($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="products.csv"');

        $output = fopen('php://output', 'w');

        fputcsv($output, ['ID', 'Название', 'Описание', 'Цена', 'Категория', 'URL изображения']);

        foreach ($products as $product) {
            fputcsv($output, [
                $product['id'],
                $product['name'],
                $product['description'],
                $product['price'],
                $product['category_name'] ?? '',
                $product['image_url'] ?? '',
            ]);
        }

        fclose($output);

        exit();
    }

    public function sitemap()
    {
        $products = $this->repository->findAll();

        header('Content-Type: application/xml; charset=utf-8');

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($products as $product) {
            echo '<url>';
            echo '<loc>http://localhost/index.php?action=show&amp;id=' . $product['id'] . '</loc>';
            echo '<changefreq>weekly</changefreq>';
            echo '<priority>0.8</priority>';
            echo '</url>';
        }
        
        echo '</urlset>';
        exit;

    }
    
    private function render($view, $data = [])
    {
        extract($data);
        $content = __DIR__ . '/../../templates/' . $view . '.phtml';
        require __DIR__ . '/../../templates/admin_layout.phtml';
    }
}