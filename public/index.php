<?php
require dirname(__DIR__) . '/src/bootstrap.php';
authenticate();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$segments = $path === '' ? [] : explode('/', $path);
if (($segments[0] ?? '') !== 'tasks') jsonResponse(['error'=>'Not Found'], 404);

$pdo = db();
$id = isset($segments[1]) && ctype_digit($segments[1]) ? (int)$segments[1] : null;

if ($method === 'GET' && $id === null) {
    $status = $_GET['status'] ?? null;
    if ($status && !in_array($status, ['todo','doing','done'], true)) jsonResponse(['error'=>'status inválido'], 422);
    $sql = 'SELECT * FROM tasks'; $params=[];
    if ($status) { $sql .= ' WHERE status=?'; $params[]=$status; }
    $sql .= ' ORDER BY id DESC'; $stmt=$pdo->prepare($sql); $stmt->execute($params); $rows=$stmt->fetchAll();
    jsonResponse(['data'=>$rows, 'count'=>count($rows)]);
}

if ($method === 'GET' && $id !== null) {
    $stmt=$pdo->prepare('SELECT * FROM tasks WHERE id=?'); $stmt->execute([$id]); $task=$stmt->fetch();
    if (!$task) jsonResponse(['error'=>'Task not found'], 404); jsonResponse(['data'=>$task]);
}

if ($method === 'POST' && $id === null) {
    [$errors,$data]=validateTask(body()); if ($errors) jsonResponse(['error'=>'Validation failed','fields'=>$errors],422);
    $stmt=$pdo->prepare('INSERT INTO tasks (title,description,priority,status,due_date) VALUES (?,?,?,?,?)'); $stmt->execute($data);
    $newId=(int)$pdo->lastInsertId(); $stmt=$pdo->prepare('SELECT * FROM tasks WHERE id=?'); $stmt->execute([$newId]); jsonResponse(['message'=>'Task created','data'=>$stmt->fetch()],201);
}

if ($method === 'PUT' && $id !== null) {
    $check=$pdo->prepare('SELECT id FROM tasks WHERE id=?'); $check->execute([$id]); if (!$check->fetch()) jsonResponse(['error'=>'Task not found'],404);
    [$errors,$data]=validateTask(body()); if ($errors) jsonResponse(['error'=>'Validation failed','fields'=>$errors],422);
    $stmt=$pdo->prepare('UPDATE tasks SET title=?,description=?,priority=?,status=?,due_date=? WHERE id=?'); $stmt->execute([...$data,$id]);
    $stmt=$pdo->prepare('SELECT * FROM tasks WHERE id=?'); $stmt->execute([$id]); jsonResponse(['message'=>'Task updated','data'=>$stmt->fetch()]);
}

if ($method === 'DELETE' && $id !== null) {
    $stmt=$pdo->prepare('DELETE FROM tasks WHERE id=?'); $stmt->execute([$id]); if ($stmt->rowCount()===0) jsonResponse(['error'=>'Task not found'],404); jsonResponse(['message'=>'Task deleted']);
}

jsonResponse(['error'=>'Method Not Allowed'],405);
