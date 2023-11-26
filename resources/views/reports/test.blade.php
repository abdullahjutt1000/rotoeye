$totalTime = (strtotime($endDateTime) - strtotime($startDateTime))/60;

$idleErrors = Error::select('id')->where('category', '=', 'Idle Time')->get();

foreach($idleErrors as $idleError){
if($idleError->id == $records[$i]->error_id){
$idleTime += $duration;
}
}


$data['budgetedTime'] = $totalTime - $idleTime;
