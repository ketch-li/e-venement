<?php

/**
 * ManifestationPictureTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ManifestationPictureTable extends PluginManifestationPictureTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object ManifestationPictureTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('ManifestationPicture');
    }
}