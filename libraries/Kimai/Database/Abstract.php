<?php

class Kimai_Database_Abstract
{
    /**
     * @var MySQL
     */
    protected $conn;

    /**
     * Kimai Global Array
     *
     * @var array
     */
    protected $kga;

    /**
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->kga['server_prefix'];
    }

    /**
     * @return string tablename including prefix
     */
    public function getProjectTable()
    {
        return $this->kga['server_prefix'] . 'projects';
    }

    /**
     * @return string tablename including prefix
     */
    public function getActivityTable()
    {
        return $this->kga['server_prefix'] . 'activities';
    }

    /**
     * @return string tablename including prefix
     */
    public function getCustomerTable()
    {
        return $this->kga['server_prefix'] . 'customers';
    }

    /**
     * @return string tablename including prefix
     */
    public function getTimeSheetTable()
    {
        return $this->kga['server_prefix'] . 'time_sheet';
    }

    /**
     * @return string tablename including prefix
     */
    public function getExpenseTable()
    {
        return $this->kga['server_prefix'] . 'expenses';
    }

    /**
     * @return string tablename including prefix
     */
    public function getUserTable()
    {
        return $this->kga['server_prefix'] . 'users';
    }

    /**
     * @return string tablename including prefix
     */
    public function getGroupsUsersTable()
    {
        return $this->kga['server_prefix'] . 'groups_users';
    }

    /**
     * @return string tablename including prefix
     */
    public function getPreferencesTable()
    {
        return $this->kga['server_prefix'] . 'preferences';
    }

    /**
     * @return string tablename including prefix
     */
    public function getRatesTable()
    {
        return $this->kga['server_prefix'] . 'rates';
    }
}