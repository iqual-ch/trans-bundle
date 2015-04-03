<?php

namespace TransBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TransBundle\Command\ImportCommand;
use TransBundle\Entity\MessageRepository;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /* @var $repository MessageRepository */
        $repository = $this->getDoctrine()->getManager()->getRepository('TransBundle:Message');
        $query = $request->query->get('q', '');
        $page = $request->query->get('page', 1);
        $perPage = $this->container->getParameter('trans.items_per_page'); 
        
        $messages = $repository->search($query, $perPage, $page - 1);
        
        return $this->render('TransBundle:Default:index.html.twig', array(
            'query' => $query,
            'messages' => $messages,
            'total' => $messages->count(),
            'per_page' => $perPage,
            'locales' => $this->container->getParameter('trans.locales'),
            'layout' => $this->container->getParameter('trans.layout')
        ));
    }
    
    /**
     * 
     * @param Request $request
     */
    public function saveAction(Request $request)
    {
        /* @var $repository MessageRepository */
        $repository = $this->getDoctrine()->getManager()->getRepository('TransBundle:Translation');
        $messages = $request->request->get('messages');
        $this->getDoctrine()->getConnection()->beginTransaction();
        foreach ($messages as $id => $locales) {
            foreach ($locales as $locale => $text) {
                $repository->updateTranslation($id, $locale, $text);
            }
        }
        $this->getDoctrine()->getConnection()->commit();
        
        $this->addFlash('success', 'message.translations_saved');
        $this->clearCacheAction();
        return $this->redirectToRoute('trans_gui', array('q' => $request->request->get('q')));
    }
    
    public function deleteAction(Request $request)
    {
        $entity = $this->getDoctrine()->getManager()->getRepository('TransBundle:Message')->find($request->query->get('id'));
        $this->getDoctrine()->getManager()->remove($entity);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('trans_gui');
    }
    
    public function clearCacheAction()
    {
        $path = '../app/cache/' . $this->get('kernel')->getEnvironment() . '/translations/*.php';
        $files = glob($path);
        foreach ($files as $file) {
            if (is_file($file) && is_writable($file)) {
                unlink($file);
            }
        }
        return $this->redirectToRoute('trans_gui');
    }
    
    public function importAction()
    {
        $command = new ImportCommand;
        $command->setContainer($this->container);
        $input = new ArrayInput(array());
        $output = new BufferedOutput();
        $code = $command->run($input, $output);
        
        return $this->render('TransBundle:Default:import.html.twig', array(
            'code' => $code,
            'log' => $output->fetch(),
            'layout' => $this->container->getParameter('trans.layout')
        ));
    }
}
