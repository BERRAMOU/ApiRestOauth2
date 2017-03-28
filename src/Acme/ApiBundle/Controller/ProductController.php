<?php
namespace Acme\ApiBundle\Controller;

use Acme\ApiBundle\Form\Type\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use \FOS\RestBundle\View\View;
use  Acme\ApiBundle\Entity\Product;
use FOS\RestBundle\Controller\Annotations as Rest;


class ProductController extends Controller
{


    /**
     * @Rest\View()
     * @Rest\Get("/api/products")
     */

    public function getProductsAction(Request $request)
    {
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Product');

        $products = $repository->findAll();

        return $products;
    }


    /**
     * @Rest\View()
     * @Rest\Get("/api/products/{id}")
     */
    public function getProductAction($id, Request $request)
    {
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Product');

        $product = $repository->find($id);

        if (empty($product)) {

            return View::create(['message' => 'product not found'], Response::HTTP_NOT_FOUND);

        }

        return $product;
    }


    /**
     *
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/api/products")
     *
     */

    public function postProductAction(Request $request)
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);

        $form->submit($request->request->all());

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return $product;

        } else {

            return $form;
        }

    }


    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/api/products/{id}")
     */
    public function removePlaceAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $product = $em->getRepository('AppBundle:Product')
            ->find($request->get('id'));

        if ($product) {

            $em->remove($product);
            $em->flush();

            return $product;

        } else {

            return View::create(['message' => 'product not found'], Response::HTTP_NOT_FOUND);

        }


    }


    /**
     * @Rest\View()
     * @Rest\Put("/api/products/{id}")
     */
    public function updateProductAction(Request $request)
    {
        return $this->updateProduct($request, true);
    }

    /**
     * @Rest\View()
     * @Rest\Patch("/api/products/{id}")
     */
    public function patchProductAction(Request $request)
    {
        return $this->updateProduct($request, false);
    }


    public function updateProduct(Request $request, $clearMissing)
    {
        $product = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Product')
            ->find($request->get('id'));

        if (empty($product)) {
            //return new JsonResponse(['message' => 'product not found'], Response::HTTP_NOT_FOUND);
            return View::create(['message' => 'product not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(ProductType::class, $product);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {

            $em = $this->get('doctrine.orm.entity_manager');
            $em->merge($product);
            $em->flush();
            return $product;

        } else {

            return $form;

        }
    }

}