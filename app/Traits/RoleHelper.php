<?php

namespace App\Traits;

use App\Models\Roles;

trait RoleHelper
{
    /**
     * Check if user has a specific role
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole($roleName)
    {
        if (!$this->role_id) {
            return false;
        }

        $role = Roles::find($this->role_id);
        return $role && $role->fixed_role === $roleName;
    }

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is content creator
     *
     * @return bool
     */
    public function isContentCreator()
    {
        return $this->hasRole('content_creator');
    }

    /**
     * Check if user is manager
     *
     * @return bool
     */
    public function isManager()
    {
        return $this->hasRole('manager');
    }

    /**
     * Check if user is tester
     *
     * @return bool
     */
    public function isTester()
    {
        return $this->hasRole('tester');
    }

    /**
     * Check if user is regular user
     *
     * @return bool
     */
    public function isUser()
    {
        return $this->hasRole('user');
    }

    /**
     * Check if user can manage products (admin or content creator)
     *
     * @return bool
     */
    public function canManageProducts()
    {
        return $this->isAdmin() || $this->isContentCreator();
    }

    /**
     * Check if user can delete products (admin only)
     *
     * @return bool
     */
    public function canDeleteProducts()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user has admin privileges (admin, content creator, or manager)
     *
     * @return bool
     */
    public function hasAdminPrivileges()
    {
        return $this->isAdmin() || $this->isContentCreator() || $this->isManager();
    }

    /**
     * Get user's role name
     *
     * @return string|null
     */
    public function getRoleName()
    {
        if (!$this->role_id) {
            return null;
        }

        $role = Roles::find($this->role_id);
        return $role ? $role->name : null;
    }

    /**
     * Get user's fixed role
     *
     * @return string|null
     */
    public function getFixedRole()
    {
        if (!$this->role_id) {
            return null;
        }

        $role = Roles::find($this->role_id);
        return $role ? $role->fixed_role : null;
    }
} 