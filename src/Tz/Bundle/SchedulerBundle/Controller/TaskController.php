<?php

namespace Tz\Bundle\SchedulerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Tz\Bundle\SchedulerBundle\Entity\Task;
use Tz\Bundle\SchedulerBundle\Form\TaskType;


/**
 * Task controller.
 *
 */
class TaskController extends Controller
{

    /**
     * Lists all Task entities.
     *
     */


    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('t')
            ->from('TzSchedulerBundle:Task', 't');

        $query = $qb->getQuery();


        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1),
            5
        );

        return $this->render('TzSchedulerBundle:Task:index.html.twig', array(
                'pagination' => $pagination
            )
        );

    }


    public function calendarAction(Request $request)
    {
        $month = $request->query->get('month');
        $year = $request->query->get('year');

        if ($month<1 or $month>12) {$month = 8;}
        if (!isset($year)) {$year = 2015;}

        $running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
        $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('TzSchedulerBundle:Task')->findAll();

        return $this->render('TzSchedulerBundle:Task:calendar.html.twig', array(
            'entities' => $entities,
            'running_day' => $running_day,
            'days_in_month' => $days_in_month,
            'month' => $month,
            'year' => $year,
        ));
    }

    /**
     * Creates a new Task entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Task();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('task_show', array('id' => $entity->getId())));
        }

        return $this->render('TzSchedulerBundle:Task:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    public function mainAction(Request $request)
    {
        for ($i = 2015; $i <= 2025; $i++) {
            $key = (string)$i;
            $years[$key] = $i;
        }

        $form = $this->createFormBuilder()
            ->add('month', 'choice', array(
                'choices' => array('1' => 'January', '2' => 'February', '3' => 'March',
                    '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November ', '12' => 'December '),
                'required' => false))
            ->add('year', 'choice', array(
                'choices' => $years,
                'required' => false))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();


            return $this->redirect($this->generateUrl('task_calendar', array('month' => $data['month'], 'year' => $data['year'])));
        }

        return $this->render('TzSchedulerBundle:Task:main.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Task entity.
     *
     * @param Task $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Task $entity)
    {
        $form = $this->createForm(new TaskType(), $entity, array(
            'action' => $this->generateUrl('task_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    private function createCreateFromCalendarForm(Task $entity)
    {
        $form = $this->createForm(new TaskType(), $entity, array(
            'action' => $this->generateUrl('task_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Task entity.
     *
     */
    public function newAction(Request $request)
    {
        $entity = new Task();
        $form = $this->createCreateForm($entity);

        $d = $this->get('request')->get('day');
        $m = $this->get('request')->get('month');
        $y = $this->get('request')->get('year');

        if (isset($d) and isset($d) and isset($d)) {
            return $this->render('TzSchedulerBundle:Task:newFromCalendar.html.twig', array(
                'entity' => $entity,
                'd' => $d,
                'm' => $m,
                'y' => $y,
                'form' => $form->createView(),
            ));
        } else {
            return $this->render('TzSchedulerBundle:Task:new.html.twig', array(
                'entity' => $entity,
                'form' => $form->createView(),
            ));
        }

    }



    /**
     * Finds and displays a Task entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TzSchedulerBundle:Task')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TzSchedulerBundle:Task:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Task entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TzSchedulerBundle:Task')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TzSchedulerBundle:Task:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Task entity.
     *
     * @param Task $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Task $entity)
    {
        $form = $this->createForm(new TaskType(), $entity, array(
            'action' => $this->generateUrl('task_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Task entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TzSchedulerBundle:Task')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Task entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('task_edit', array('id' => $id)));
        }

        return $this->render('TzSchedulerBundle:Task:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Task entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('TzSchedulerBundle:Task')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Task entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('task'));
    }

    /**
     * Creates a form to delete a Task entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('task_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }
}
