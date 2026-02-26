```php
<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $r)
    {
        $query = Employee::query();

        if ($search = $r->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy(
            $r->input('sort_by', 'created_at'),
            $r->input('sort_dir', 'desc')
        )->paginate($r->input('per_page', 15));
    }

    public function store(Request $r)
    {
        $validatedData = $r->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees',
            'phone' => 'nullable|string|max:25',
            'position' => 'nullable|string|max:255',
        ]);

        $employee = Employee::create($validatedData);

        return response()->json($employee, 201);
    }

    public function show($id)
    {
        return Employee::findOrFail($id);
    }

    public function update(Request $r, $id)
    {
        $e = Employee::findOrFail($id);

        $validatedData = $r->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('employees')->ignore($e->id),
            ],
            'phone' => 'nullable|string|max:25',
            'position' => 'nullable|string|max:255',
        ]);

        $e->update($validatedData);
        
        return $e;
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->noContent();
    }
}
```