```php
<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;

class LeaveController extends Controller
{
    public function index()
    {
        return LeaveRequest::latest()->paginate();
    }

    public function show($id)
    {
        return LeaveRequest::findOrFail($id);
    }

    public function apply(Request $r){
        $data = $r->all();
        // Ensure new requests always start as pending
        $data['status'] = 'pending';
        return LeaveRequest::create($data);
    }

    public function approve($id){
        $leave=LeaveRequest::findOrFail($id);
        $leave->update(['status'=>'approved']);
        return $leave;
    }

    public function reject($id){
        $leave=LeaveRequest::findOrFail($id);
        $leave->update(['status'=>'rejected']);
        return $leave;
    }

    public function destroy($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->delete();

        return response()->json(null, 204);
    }
}
```