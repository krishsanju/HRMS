```php
<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DepartmentController extends Controller
{
    public function index()
    {
        return DepartmentResource::collection(Department::paginate());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
        ]);

        $department = Department::create($validated);

        return (new DepartmentResource($department))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Department $department)
    {
        return new DepartmentResource($department);
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        $department->update($validated);

        return new DepartmentResource($department);
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
```