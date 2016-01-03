<?php

namespace App\Http\Controllers;

use Auth;
use Gate;
use JWTAuth;
use App\User;
use App\Territory;
use App\Address;
use App\Note;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class TerritoriesController extends ApiController
{    
   	public function index(Request $request) {
		if ( ! $this->hasAccess($request) ) {
			return Response()->json(['error' => 'Access denied.'], 500);
		}
		
		return ['data' => $this->transformCollection(Territory::latest()->get(), 'territory')];
   	} 
   	
   	public function view(Request $request, $territoryId = null) {
		if ( ! $this->hasAccess($request) ) {
			return Response()->json(['error' => 'Access denied.'], 500);
		}
		
		try {
	        $territory = Territory::where('id', $territoryId)->with(['publisher', 'addresses.notes' => function ($query) {
			    $query->orderBy('date', 'desc');
			}])->get();
	        $data = !empty($territory[0]) ? $this->transform($territory[0]->toArray(), 'territory') : null;
        } catch (Exception $e) {
        	$data = ['error' => 'Territory not found', 'message' => $e->getMessage()];
		}
		return ['data' => $data];
   	}  	
   	
   	public function save(Request $request, $territoryId = null) {
		if ( ! $this->hasAccess($request) ) {
			return Response()->json(['error' => 'Access denied.'], 500);
		}
		
		if (Gate::denies('update-territories')) {
            return Response()->json(['error' => 'Method not allowed'], 403);
        }
		
		if(!empty($territoryId)) {
    		// dd($this->unTransform($request->all(), 'territory'));
	        try {
		        $territory = Territory::findOrFail($territoryId);
		        $data = $territory->update($this->unTransform($request->all(), 'territory'));
	        } catch (Exception $e) {
	        	$data = ['error' => 'Territory not updated', 'message' => $e->getMessage()];
			}
		}
		return ['data' => $data];
   	} 
   	
   	public function saveAddress(Request $request, $territoryId = null, $addressId = null) {
		if ( ! $this->hasAccess($request) ) {
			return Response()->json(['error' => 'Access denied.'], 500);
		}
		 
        if(empty($territoryId)) {
            return ['error' => 'Territory not found', 'message' => 'Territory not found'];
        }
		
		if(!empty($addressId)) {
			if (Gate::denies('update-addresses')) {
	            return Response()->json(['error' => 'Method not allowed'], 403);
	        }
	        
	        // dd($this->unTransform($request->all(), 'address'));
	        try {
		        $address = Address::findOrFail($addressId);
		        $data = $address->update($this->unTransform($request->all(), 'address'));
	        } catch (Exception $e) {
	        	$data = ['error' => 'Address not updated', 'message' => $e->getMessage()];
			}
		} else {
			if (Gate::denies('create-addresses')) {
	            return Response()->json(['error' => 'Method not allowed'], 403);
	        }
	        
	        // dd($this->unTransform($request->all(), 'address'));
	        try {
	        	$transformedData = $this->unTransform($request->all(), 'address');
	        	$territory = Territory::findOrFail($territoryId);
				$address = !empty($territory) ? $territory->addresses()->create($transformedData) : null;
				$data = ($address && !empty($transformedData['notes'])) ? $address->notes()->create($transformedData['notes'][0]) : null;
	        } catch (Exception $e) {
	        	$data = ['error' => 'Address not updated', 'message' => $e->getMessage()];
			}
		}
		return ['data' => $data];
   	}
   	
   	public function removeAddress(Request $request, $addressId = null) {
		if ( ! $this->hasAccess($request) ) {
			return Response()->json(['error' => 'Access denied.'], 500);
		}
		
		if (Gate::denies('delete-addresses')) {
            return Response()->json(['error' => 'Method not allowed'], 403);
        }
        
        if(empty($addressId)) {
            return ['error' => 'Address not found', 'message' => 'Address not found'];
        }
		
		// dd($this->unTransform($request->all(), 'address'));
        try {
	        $address = Address::findOrFail($addressId);
	        $data = $address->delete();
        } catch (Exception $e) {
        	$data = ['error' => 'Address not updated', 'message' => $e->getMessage()];
		}
		
   	}
   	
   	public function addNote(Request $request, $territoryId = null, $addressId = null) {
		if ( ! $this->hasAccess($request) ) {
			return Response()->json(['error' => 'Access denied.'], 500);
		}
        
        if(empty($territoryId)) {
            return ['error' => 'Territory not found', 'message' => 'Territory not found'];
        }
		
		if(!empty($addressId)) {
			if (Gate::denies('create-notes')) {
	            return Response()->json(['error' => 'Method not allowed'], 403);
	        }
	        
	        // dd($this->unTransform($request->all(), 'note'));
	        try {
	        	$transformedData = $this->unTransform($request->all(), 'note');
	        	$address = Address::findOrFail($addressId);
				$data = ($address && !empty($transformedData)) ? $address->notes()->create($transformedData) : null;
	        } catch (Exception $e) {
	        	$data = ['error' => 'Address not updated', 'message' => $e->getMessage()];
			}
		} else {
			$data = ['error' => 'Address not found', 'message' => 'Address not found'];
		}
		return ['data' => $data];
   	}
   	
   	public function saveNote(Request $request, $territoryId = null, $noteId = null) {
		if ( ! $this->hasAccess($request) ) {
			return Response()->json(['error' => 'Access denied.'], 500);
		}
        
        if(empty($territoryId)) {
            return ['error' => 'Territory not found', 'message' => 'Territory not found'];
        }
		
		if(!empty($noteId)) {
			if (Gate::denies('update-notes')) {
	            return Response()->json(['error' => 'Method not allowed'], 403);
	        }
	        
	        // dd($this->unTransform($request->all(), 'note'));
	        try {
		        $note = Note::findOrFail($noteId);
		        $data = $note->update($this->unTransform($request->all(), 'note'));
	        } catch (Exception $e) {
	        	$data = ['error' => 'Note not updated', 'message' => $e->getMessage()];
			}
		} else {
			$data = ['error' => 'Note not found', 'message' => 'Note not found'];
		}
		return ['data' => $data];
   	}
}

