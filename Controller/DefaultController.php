<?php

namespace TransBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Command\CacheClearCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use TransBundle\Command\ImportCommand;
use TransBundle\Entity\MessageRepository;
use TransBundle\Type\FilterType;

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
        
        $form = $this->createForm(new FilterType($repository->getDomains(), $this->container->getParameter('locales')));
        $form->submit($request->query->all());
        
        $criterias = $form->getData();
        $options = array(
            'current_page' => $request->query->get('page', 1) - 1,
            'per_page' => $this->container->getParameter('trans.items_per_page')
        );
        
        $messages = $repository->search($criterias, $options);
        
        return $this->render('TransBundle:Default:index.html.twig', array(
            'messages' => $messages,
            'per_page' => $options['per_page'],
            'locales' => $this->container->getParameter('trans.locales'),
            'layout' => $this->container->getParameter('trans.layout'),
            'form' => $form->createView()
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
        
        $this->addFlash('success', $this->get('translator')->trans('message.translations_saved', array(), 'TransBundle'));
        return $this->redirectToRoute('trans_gui', $request->query->all());
    }
    
    public function deleteAction(Request $request)
    {
        $entity = $this->getDoctrine()->getManager()->getRepository('TransBundle:Message')->find($request->query->get('id'));
        $this->getDoctrine()->getManager()->remove($entity);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('trans_gui', $request->query->all());
    }
    
    public function clearCacheAction(Request $request)
    {
        $command = new CacheClearCommand;
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
    
    public function clearGarbageAction(Request $request)
    {
        $this->getDoctrine()->getManager()->getRepository('TransBundle:Message')->clearGarbage();
        $this->addFlash('success', $this->get('translator')->trans('message.garbage_cleared', array(), 'TransBundle'));
        return $this->redirectToRoute('trans_gui', $request->query->all());
    }
}
