<?php
namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ODM\Document */
class Manager extends BaseEmployee
{
    /** @ODM\ReferenceMany(targetDocument="Documents\Project") */
    private $projects;

    public function __construct() { $this->projects = new ArrayCollection(); }

    public function getProjects() { return $this->projects; }
    public function addProject(Project $project) { $this->projects[] = $project; }
}
