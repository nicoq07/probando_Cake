<h2 align='center'> Bienvenido <?= $this->Html->link($current_user['nombre'], ['controller' => 'Users', 'action' => 'view', $current_user['id']])?></h2>
<h3 align='center'> Tenes dos notificaciones</h3>