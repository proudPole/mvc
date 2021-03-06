<?php
/**
 * Created by PhpStorm.
 * User: bartek
 * Date: 15.10.18
 * Time: 12:02
 */

class Posts extends Controller
{
    private $postModel;
    private $userModel;

    public function __construct()
    {
        if(!isLoggedIn()) {
            redirect('users/login');
        }

        $this->postModel = $this->model('Post');
        $this->userModel = $this->model('User');
    }

    public function index()
    {
        //Get posts
        $posts = $this->postModel->getPosts();

        $data = [
            'posts' => $posts
        ];

        $this->view('posts/index', $data);
    }

    public function add()
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            //Sanitize POST array
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'title' => trim($_POST['title']),
                'body' => trim($_POST['body']),
                'user_id' => $_SESSION['user_id'],
                'title_error' => '',
                'body_error' => ''
            ];

            //Validate title
            if(empty($data['title'])) {
                $data['title_error'] = 'Please enter title';
            }

            if(empty($data['body'])) {
                $data['body_error'] = 'Please enter body text';
            }

            //Make sure no errors
            if(empty($data['title_error']) && empty($data['body_error'])) {
                //Validated
                if($this->postModel->addPost($data)) {
                    flash('post_message', 'Post Added');
                    redirect('posts');
                } else {
                    die('Something went wrong');
                }
            } else {
                //Load view with errors
                $this->view('posts/add', $data);
            }

        } else {
            $data = [
                'title' => '',
                'body' => ''
            ];

            $this->view('posts/add', $data);
        }
    }

    public function show(int $id)
    {
        $post = $this->postModel->getPostById($id);
        $user = $this->userModel->getUserById($post->user_id);

        $data = [
            'id' => $post->id_posts,
            'title' => $post->title,
            'body' => $post->body,
            'created_at' => $post->created_at,
            'user_id' => $user->id_users,
            'user_name' => $user->name,

        ];

        $this->view('posts/show', $data);
    }

    public function edit(int $id)
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            //Sanitize POST array
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'id' => $id,
                'title' => trim($_POST['title']),
                'body' => trim($_POST['body']),
                'user_id' => $_SESSION['user_id'],
                'title_error' => '',
                'body_error' => ''
            ];

            //Validate title
            if(empty($data['title'])) {
                $data['title_error'] = 'Please enter title';
            }

            if(empty($data['body'])) {
                $data['body_error'] = 'Please enter body text';
            }

            //Make sure no errors
            if(empty($data['title_error']) && empty($data['body_error'])) {
                //Validated
                if($this->postModel->updatePost($data)) {
                    flash('post_message', 'Post Update');
                    redirect('posts');
                } else {
                    die('Something went wrong');
                }
            } else {
                //Load view with errors
                $this->view('posts/edit', $data);
            }

        } else {

            $post = $this->postModel->getPostById($id);

            //Check for owner
            if($post->user_id !== $_SESSION['user_id']) {
                redirect('posts');
            }

            $data = [
                'id' => $id,
                'title' => $post->title,
                'body' => $post->body
            ];

            $this->view('posts/edit', $data);
        }
    }
}