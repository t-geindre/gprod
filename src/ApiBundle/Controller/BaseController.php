<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ApiBundle\Entity\User;

/**
 * Api base controller
 */
class BaseController extends Controller
{
    /**
     * @param string          $message
     * @param \Exception|null $previous
     */
    protected function createBadRequestException($message = 'Bad request', \Exception $previous = null)
    {
        return new BadRequestHttpException($message, $previous);
    }

    /**
     * @param Request       $request
     * @param object        $type
     * @param object        $entity
     * @param Callable|null $prePersist
     *
     * @return array
     */
    protected function handleForm(Request $request, $type, $entity, callable $prePersist = null) : array
    {
        $form = $this->createForm($type, $entity);

        $form->submit(
            json_decode($request->getContent(), true)
        );

        $result = ['entity' => $entity];

        if ($valid = $form->isValid()) {
            $em = $this->get('doctrine')->getManager();
            if (!is_null($prePersist)) {
                call_user_func($prePersist, $entity);
            }
            $em->persist($entity);
            $em->flush();
        } else {
            $result['errors'] = $this
                ->get('api_bundle.serializer.form.error')
                ->serializeErrors($form)
            ;
        }

        return $result;
    }
}
