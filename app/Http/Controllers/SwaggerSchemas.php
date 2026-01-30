<?php

namespace App\Http\Controllers;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="username", type="string", example="admin"),
 *     @OA\Property(property="email", type="string", format="email", example="admin@kmc.go.tz"),
 *     @OA\Property(property="full_name", type="string", example="John Doe"),
 *     @OA\Property(property="position", type="string", example="System Administrator"),
 *     @OA\Property(property="department", type="string", example="IT Department"),
 *     @OA\Property(property="org_unit", type="string", example="Kibaha Municipal Council"),
 *     @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"Admin", "Super Admin"}),
 *     @OA\Property(property="permissions", type="array", @OA\Items(type="string"), example={"view_users", "manage_projects"}),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="last_login", type="string", format="date-time", example="2026-01-28T10:30:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-28T10:30:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Project",
 *     title="Project",
 *     description="Project model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="School Construction Project"),
 *     @OA\Property(property="description", type="string", example="Construction of new primary school in Kibaha"),
 *     @OA\Property(property="code", type="string", example="KMC/EDU/2026/001"),
 *     @OA\Property(property="status", type="string", enum={"PLANNING", "ACTIVE", "COMPLETED", "SUSPENDED", "CANCELLED"}, example="ACTIVE"),
 *     @OA\Property(property="priority", type="string", enum={"LOW", "MEDIUM", "HIGH", "CRITICAL"}, example="HIGH"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2026-01-01"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2026-12-31"),
 *     @OA\Property(property="budget", type="number", format="float", example=50000000.00),
 *     @OA\Property(property="actual_cost", type="number", format="float", example=25000000.00),
 *     @OA\Property(property="progress_percentage", type="number", format="float", example=50.5),
 *     @OA\Property(property="theme_id", type="integer", example=1),
 *     @OA\Property(property="org_unit_id", type="integer", example=1),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="theme", ref="#/components/schemas/Theme"),
 *     @OA\Property(property="organizational_unit", ref="#/components/schemas/OrganizationalUnit"),
 *     @OA\Property(property="creator", ref="#/components/schemas/User"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-28T10:30:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Theme",
 *     title="Theme",
 *     description="Development theme model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Education"),
 *     @OA\Property(property="description", type="string", example="Educational development projects"),
 *     @OA\Property(property="code", type="string", example="EDU"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-28T10:30:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="OrganizationalUnit",
 *     title="Organizational Unit",
 *     description="Organizational unit model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Kibaha Municipal Council"),
 *     @OA\Property(property="code", type="string", example="KMC"),
 *     @OA\Property(property="type", type="string", enum={"REGION", "DISTRICT", "WARD", "VILLAGE", "FACILITY"}, example="DISTRICT"),
 *     @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-28T10:30:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Expenditure",
 *     title="Expenditure",
 *     description="Expenditure model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="project_id", type="integer", example=1),
 *     @OA\Property(property="amount", type="number", format="float", example=15000000.00),
 *     @OA\Property(property="description", type="string", example="Building materials purchase"),
 *     @OA\Property(property="category", type="string", example="Materials"),
 *     @OA\Property(property="status", type="string", enum={"PENDING", "APPROVED", "VERIFIED", "REJECTED"}, example="APPROVED"),
 *     @OA\Property(property="expenditure_date", type="string", format="date", example="2026-01-15"),
 *     @OA\Property(property="receipt_number", type="string", example="RCP-2026-001"),
 *     @OA\Property(property="project", ref="#/components/schemas/Project"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-15T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-28T10:30:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Indicator",
 *     title="Indicator",
 *     description="Performance indicator model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="School Enrollment Rate"),
 *     @OA\Property(property="description", type="string", example="Percentage of school-age children enrolled"),
 *     @OA\Property(property="code", type="string", example="EDU-001"),
 *     @OA\Property(property="unit", type="string", example="%"),
 *     @OA\Property(property="target_value", type="number", format="float", example=95.0),
 *     @OA\Property(property="baseline_value", type="number", format="float", example=85.0),
 *     @OA\Property(property="theme_id", type="integer", example=1),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="theme", ref="#/components/schemas/Theme"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-28T10:30:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Notification",
 *     title="Notification",
 *     description="Notification model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Project Update"),
 *     @OA\Property(property="message", type="string", example="Project status has been updated to Active"),
 *     @OA\Property(property="type", type="string", enum={"INFO", "SUCCESS", "WARNING", "ERROR"}, example="INFO"),
 *     @OA\Property(property="is_read", type="boolean", example=false),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-28T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-28T10:30:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     title="Error Response",
 *     description="Standard error response format",
 *     @OA\Property(property="status", type="string", example="error"),
 *     @OA\Property(property="message", type="string", example="Error description"),
 *     @OA\Property(property="errors", type="object", example={"field": {"Error message"}})
 * )
 * 
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     title="Success Response",
 *     description="Standard success response format",
 *     @OA\Property(property="status", type="string", example="success"),
 *     @OA\Property(property="message", type="string", example="Operation completed successfully"),
 *     @OA\Property(property="data", type="object", example={"key": "value"})
 * )
 * 
 * @OA\Schema(
 *     schema="PaginatedResponse",
 *     title="Paginated Response",
 *     description="Paginated response format",
 *     @OA\Property(property="data", type="array", @OA\Items()),
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=10),
 *     @OA\Property(property="per_page", type="integer", example=20),
 *     @OA\Property(property="total", type="integer", example=200),
 *     @OA\Property(property="from", type="integer", example=1),
 *     @OA\Property(property="to", type="integer", example=20)
 * )
 */
class SwaggerSchemas
{
    // This class contains only Swagger annotations for schemas
}
