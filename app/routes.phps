
$app->group('/v1', function() use ($container) {
	
	// LIST PDB ROUTE
	// 
	// Adding specific middleware for this page (premium, admin, etc)
	$app->get('/filess/:file_id', 'apiController:getFiles');
	$app->put('/filess/:file_id', 'apiController:getFiles');
	$app->delete('/filess/:file_id', 'apiController:getFiles');

	// GET MONOMER BY PDB
	// 
	$this->get('/pdb/mon/{id}', 'apiController:getMonPDB');

	// LIST MONOMERS ROUTE
	// 
