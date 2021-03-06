<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;


/**
 * DeudasGestiones Controller
 *
 * @property \App\Model\Table\DeudasGestionesTable $DeudasGestiones
 */
class DeudasGestionesController extends AppController
{
  public function isAuthorized($user)
  {
      if(isset($user['role_id']) and ($user['role_id'] == 2 || $user['role_id'] == 3) )
      {
          if(in_array($this->request->action, ['index','view','add']))
          {
              return true;
          }
      }
      return parent::isAuthorized($user);
  }
    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index($idDeuda = null)
    {
        $notAdmin = null;
        $notDeuda = null;

        if (!empty($idDeuda)) $notDeuda = ['deuda_id' => $idDeuda];

        if ($this->Auth->user('role_id') !== 1 && $this->Auth->user('role_id') !== 2 )
        {

             $notAdmin = ['Deudas.usuario_id' => $this->Auth->user('id')];
        }

        $this->paginate = [
          'contain' => ['Deudas'],
          'conditions' => [$notDeuda ,$notAdmin],
          'order' => ['DeudasGestiones.modified' => 'desc']];

        $deudasGestiones = $this->paginate($this->DeudasGestiones);

        if (!empty($idDeuda)) $notDeuda = $deudasGestiones->toArray()[0]['deuda'];

        $users = TableRegistry::get('Users');
        $users = $users->find('list');
        $data = ['deudasGestiones' => $deudasGestiones,'deuda' =>
                  $notDeuda,'users' => $users];
        $this->set($data);

    }
    public function search()
    {
      if (!empty($this->request->data['usuario_id']))
      {
        $user_id = $this->request->data['usuario_id'];
         $this->paginate = [
           'contain' => ['Deudas'],
           'conditions' => ['Deudas.usuario_id' => $user_id],
           'order' => ['DeudasGestiones.modified' => 'desc'],
           'limit' => 9999];
         $deudasGestiones = $this->paginate($this->DeudasGestiones);
         $users = TableRegistry::get('Users');
         $user = $users->get($user_id);
         $data = ['deudasGestiones' => $deudasGestiones,'user' => $user];
         $this->set($data);
      }
      else {
          return $this->redirect(['action' => 'index']);
      }


    }

    /**
     * View method
     *
     * @param string|null $id Deudas Gestione id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $deudasGestione = $this->DeudasGestiones->get($id, [
            'contain' => ['Deudas']
        ]);

        $this->set('deudasGestione', $deudasGestione);
        $this->set('_serialize', ['deudasGestione']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add($deuda_id = null)
    {
        $cabecera = 'Operador '. $this->Auth->user('nombre')." ".$this->Auth->user('apellido') . ' ha escrito: ' ;

      if (empty($deuda_id))
        {
          return false;
        }

        $deudasGestione = $this->DeudasGestiones->newEntity();
        $deuda = $this->DeudasGestiones->Deudas->get($deuda_id);
        $deudasGestione->deuda_id = $deuda_id;

        if ($this->request->is('post')) {



            $deudasGestione = $this->DeudasGestiones->patchEntity($deudasGestione, $this->request->data);
            $deudasGestione['descripcion'] = $cabecera.$deudasGestione['descripcion'];
            $connection = ConnectionManager::get('default');

            if ($this->request->data['estado_id'] == 6)//estado acuerodepago debe ser id 6
      			{
      				$connection->update('deudas', ['estado_id' => $this->request->data['estado_id'] , 'acuerdo' => true ], ['id' => $deudasGestione->deuda_id]);
      			}
      			else
      			{
      				$connection->update('deudas', ['estado_id' => $this->request->data['estado_id'] ], ['id' => $deudasGestione->deuda_id]);
      			}

            if ($this->DeudasGestiones->save($deudasGestione)) {
                $this->Flash->success(__('Gestión guardada.'));

                return $this->redirect(['controller' => 'Deudas' ,'action' => 'view/'.$deuda_id]);
            }
            $this->Flash->error(__('Error al guardar la gestión. Reintente.'));
        }
        $estados_deuda = $this->DeudasGestiones->Deudas->EstadosDeudas->find('list', ['limit' => 200]);
        $deudas = $this->DeudasGestiones->Deudas->find('list', ['limit' => 200]);

        // $estado_actual_id = $estado_actual_id->estado_id;
        $this->set(compact('deudasGestione', 'deudas','estados_deuda','deuda'));
        $this->set('_serialize', ['deudasGestione']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Deudas Gestione id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $deudasGestione = $this->DeudasGestiones->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $deudasGestione = $this->DeudasGestiones->patchEntity($deudasGestione, $this->request->data);
            if ($this->DeudasGestiones->save($deudasGestione)) {
                $this->Flash->success(__('The deudas gestione has been saved.'));

                return $this->redirect(['controller' => 'Deudas' ,'action' => 'view/'.$deudasGestione->deuda_id]);
            }
            $this->Flash->error(__('The deudas gestione could not be saved. Please, try again.'));
        }
        $deudas = $this->DeudasGestiones->Deudas->find('list', ['limit' => 200]);
        $this->set(compact('deudasGestione', 'deudas'));
        $this->set('_serialize', ['deudasGestione']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Deudas Gestione id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $deudasGestione = $this->DeudasGestiones->get($id);
        if ($this->DeudasGestiones->delete($deudasGestione)) {
            $this->Flash->success(__('The deudas gestione has been deleted.'));
        } else {
            $this->Flash->error(__('The deudas gestione could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
