```php
<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        return Department::paginate();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
        ]);

        $department = Department::create($validatedData);

        return response()->json($department, 201);
    }

    public function show(Department $department)
    {
        return $department;
    }

    public function update(Request $request, Department $department)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)],
        ]);
        
        $department->update($validatedData);
        
        return $department;
    }

    public function destroy(Department $department)
    {
        $department->delete();
        
        return response()->noContent();
    }
}
```