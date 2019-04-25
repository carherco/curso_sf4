<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Editor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;

/**
 * @Route("/blog")
 */
class BlogController extends AbstractController
{
    /**
     * Entidad inicial: Post
     * Técnica: Lazy Loading
     * @Route("/ejemplo1", name="blog_ejemplo1", methods="GET")
     */
    public function ejemplo1(): Response {

        $output = array();

        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findAll();

        $output['posts'] = [];
        foreach($posts as $post) {
            $post_data = [];
            $post_data['title'] = $post->getTitle();
            $post_data['text'] = $post->getText();
            $post_data['author'] = $post->getAuthor()->getUsername();
            $post_data['comments'] = [];
            $comments = $post->getComments();
            foreach($comments as $comment) {
                $comment_data = [];
                $comment_data['text'] = $comment->getText();
                $comment_data['author'] = $comment->getAuthor()->getUsername();
                $post_data['comments'][] = $comment_data;
            }
            $post_data['categories'] = [];
            $categories = $post->getCategories();
            foreach($categories as $category) {
                $category_data = [];
                $category_data['name'] = $category->getName();
                $post_data['categories'][] = $category_data;
            }
            $output['posts'][] = $post_data;
        }
        
        return $this->json($output);
    }

    /**
     * Entidad inicial: Post
     * Técnica: 1 única SQL
     * @Route("/ejemplo2", name="blog_ejemplo2", methods="GET")
     */
    public function ejemplo2(): Response {

        $output = array();

        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findAllImproved();

        $output['posts'] = [];
        foreach($posts as $post) {
            $post_data = [];
            $post_data['title'] = $post->getTitle();
            $post_data['text'] = $post->getText();
            $post_data['author'] = $post->getAuthor()->getUsername();
            $post_data['comments'] = [];
            $comments = $post->getComments();
            foreach($comments as $comment) {
                $comment_data = [];
                $comment_data['text'] = $comment->getText();
                $comment_data['author'] = $comment->getAuthor()->getUsername();
                $post_data['comments'][] = $comment_data;
            }
            $post_data['categories'] = [];
            $categories = $post->getCategories();
            foreach($categories as $category) {
                $category_data = [];
                $category_data['name'] = $category->getName();
                $post_data['categories'][] = $category_data;
            }
            $output['posts'][] = $post_data;
        }
        
        return $this->json($output);
    }

    /**
     * Entidad inicial: Editor
     * Técnica: Lazy Loading
     * @Route("/ejemplo3", name="blog_ejemplo3", methods="GET")
     */
    public function ejemplo3(): Response {

        $output = array();

        $editors = $this->getDoctrine()
            ->getRepository(Editor::class)
            ->findAll();

        $output['editors'] = [];
        foreach($editors as $editor) {
            $editor_data = [];
            $editor_data['username'] = $editor->getUsername();
            $editor_data['posts'] = [];
            $posts = $editor->getPosts();
            foreach($posts as $post) {
                $post_data['title'] = $post->getTitle();
                $post_data['text'] = $post->getText();
                $post_data['author'] = $post->getAuthor()->getUsername();
                $post_data['comments'] = [];
                $comments = $post->getComments();
                foreach($comments as $comment) {
                    $comment_data = [];
                    $comment_data['text'] = $comment->getText();
                    $comment_data['author'] = $comment->getAuthor()->getUsername();
                    $post_data['comments'][] = $comment_data;
                }
                $post_data['categories'] = [];
                $categories = $post->getCategories();
                foreach($categories as $category) {
                    $category_data = [];
                    $category_data['name'] = $category->getName();
                    $post_data['categories'][] = $category_data;
                }
                $editor_data['posts'] = $post_data;
            }
            $output['editors'][] = $editor_data;
        }
        
        return $this->json($output);
    }

    /**
     * Entidad inicial: Editor
     * Técnica: 1 única SQL
     * @Route("/ejemplo4", name="blog_ejemplo4", methods="GET")
     */
    public function ejemplo4(): Response {

        $output = array();

        $editors = $this->getDoctrine()
            ->getRepository(Editor::class)
            ->findAllImproved();

        $output['editors'] = [];
        foreach($editors as $editor) {
            $editor_data = [];
            $editor_data['username'] = $editor->getUsername();
            $editor_data['posts'] = [];
            $posts = $editor->getPosts();
            foreach($posts as $post) {
                $post_data['title'] = $post->getTitle();
                $post_data['text'] = $post->getText();
                $post_data['author'] = $post->getAuthor()->getUsername();
                $post_data['comments'] = [];
                $comments = $post->getComments();
                foreach($comments as $comment) {
                    $comment_data = [];
                    $comment_data['text'] = $comment->getText();
                    $comment_data['author'] = $comment->getAuthor()->getUsername();
                    $post_data['comments'][] = $comment_data;
                }
                $post_data['categories'] = [];
                $categories = $post->getCategories();
                foreach($categories as $category) {
                    $category_data = [];
                    $category_data['name'] = $category->getName();
                    $post_data['categories'][] = $category_data;
                }
                $editor_data['posts'] = $post_data;
            }
            $output['editors'][] = $editor_data;
        }
        
        return $this->json($output);
    }

    /**
     * Entidad inicial: Post
     * Técnica: Multi-step Hydration
     * @Route("/ejemplo5", name="blog_ejemplo5", methods="GET")
     */
    public function ejemplo5(): Response {

        $output = array();

        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findAllMultiStep();

        $output['posts'] = [];
        foreach($posts as $post) {
            $post_data = [];
            $post_data['title'] = $post->getTitle();
            $post_data['text'] = $post->getText();
            $post_data['author'] = $post->getAuthor()->getUsername();
            $post_data['comments'] = [];
            $comments = $post->getComments();
            foreach($comments as $comment) {
                $comment_data = [];
                $comment_data['text'] = $comment->getText();
                $comment_data['author'] = $comment->getAuthor()->getUsername();
                $post_data['comments'][] = $comment_data;
            }
            $post_data['categories'] = [];
            $categories = $post->getCategories();
            foreach($categories as $category) {
                $category_data = [];
                $category_data['name'] = $category->getName();
                $post_data['categories'][] = $category_data;
            }
            $output['posts'][] = $post_data;
        }
        
        return $this->json($output);
    }

    /**
     * @Route("/ejemplo9", name="blog_ejemplo9", methods="GET")
     */
    public function ejemplo9(): Response {

        $data = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findAll();
//var_dump($data);

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer($classMetadataFactory);

        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        // all callback parameters are optional (you can omit the ones you don't use)
        $normalizer->setMaxDepthHandler(function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
            return $innerObject->idu;
        });

        $serializer = new Serializer([$normalizer], [$encoder]);
        $data_normalized = $serializer->normalize($data, null, array(ObjectNormalizer::ENABLE_MAX_DEPTH => true));
        
        
        //$data_serialized = $serializer->serialize($data, 'json', [ObjectNormalizer::ENABLE_MAX_DEPTH => true]); 

        $response = new Response();
        //$response->setContent($data_serialized);
        $response->setContent('');
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
