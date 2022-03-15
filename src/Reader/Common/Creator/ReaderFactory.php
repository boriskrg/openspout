<?php

namespace OpenSpout\Reader\Common\Creator;

use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Common\Helper\EncodingHelper;
use OpenSpout\Common\Type;
use OpenSpout\Reader\CSV\Creator\InternalEntityFactory as CSVInternalEntityFactory;
use OpenSpout\Reader\CSV\Manager\OptionsManager as CSVOptionsManager;
use OpenSpout\Reader\CSV\Reader as CSVReader;
use OpenSpout\Reader\ODS\Creator\HelperFactory as ODSHelperFactory;
use OpenSpout\Reader\ODS\Creator\InternalEntityFactory as ODSInternalEntityFactory;
use OpenSpout\Reader\ODS\Creator\ManagerFactory as ODSManagerFactory;
use OpenSpout\Reader\ODS\Manager\OptionsManager as ODSOptionsManager;
use OpenSpout\Reader\ODS\Reader as ODSReader;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\XLSX\Creator\HelperFactory as XLSXHelperFactory;
use OpenSpout\Reader\XLSX\Creator\InternalEntityFactory as XLSXInternalEntityFactory;
use OpenSpout\Reader\XLSX\Creator\ManagerFactory as XLSXManagerFactory;
use OpenSpout\Reader\XLSX\Manager\OptionsManager as XLSXOptionsManager;
use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactory;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;

/**
 * This factory is used to create readers, based on the type of the file to be read.
 * It supports CSV, XLSX and ODS formats.
 */
class ReaderFactory
{
    /**
     * Creates a reader by file extension.
     *
     * @param string $path The path to the spreadsheet file. Supported extensions are .csv,.ods and .xlsx
     *
     * @throws \OpenSpout\Common\Exception\UnsupportedTypeException
     *
     * @return ReaderInterface
     */
    public static function createFromFile(string $path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return self::createFromType($extension);
    }

    /**
     * This creates an instance of the appropriate reader, given the type of the file to be read.
     *
     * @param string $readerType Type of the reader to instantiate
     *
     * @throws \OpenSpout\Common\Exception\UnsupportedTypeException
     *
     * @return ReaderInterface
     */
    public static function createFromType($readerType)
    {
        switch ($readerType) {
            case Type::CSV: return self::createCSVReader();

            case Type::XLSX: return self::createXLSXReader();

            case Type::ODS: return self::createODSReader();

            default:
                throw new UnsupportedTypeException('No readers supporting the given type: '.$readerType);
        }
    }

    /**
     * @return CSVReader
     */
    private static function createCSVReader()
    {
        $optionsManager = new CSVOptionsManager();
        $entityFactory = new CSVInternalEntityFactory(EncodingHelper::factory());

        return new CSVReader($optionsManager, $entityFactory);
    }

    /**
     * @return XLSXReader
     */
    private static function createXLSXReader()
    {
        $optionsManager = new XLSXOptionsManager();
        $helperFactory = new XLSXHelperFactory();
        $managerFactory = new XLSXManagerFactory($helperFactory, new CachingStrategyFactory());
        $entityFactory = new XLSXInternalEntityFactory($managerFactory, $helperFactory);

        return new XLSXReader($optionsManager, $entityFactory, $managerFactory);
    }

    /**
     * @return ODSReader
     */
    private static function createODSReader()
    {
        $optionsManager = new ODSOptionsManager();
        $helperFactory = new ODSHelperFactory();
        $managerFactory = new ODSManagerFactory();
        $entityFactory = new ODSInternalEntityFactory($helperFactory, $managerFactory);

        return new ODSReader($optionsManager, $entityFactory);
    }
}