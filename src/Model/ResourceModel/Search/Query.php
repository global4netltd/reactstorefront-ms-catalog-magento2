<?php

namespace G4NReact\MsCatalogMagento2\Model\ResourceModel\Search;

use G4NReact\MsCatalogMagento2GraphQl\Helper\Parser;
use Magento\Framework\Exception\LocalizedException;
use Magento\Search\Model\Query as QueryModel;
use Magento\Search\Model\ResourceModel\Query as MagentoSearchQuery;

/**
 * Class Query
 * @package G4NReact\MsCatalogMagento2\Model\ResourceModel\Search
 */
class Query extends MagentoSearchQuery
{
    /**
     * Save query with incremental popularity
     *
     * @param QueryModel $query
     * @return void
     *
     * @throws LocalizedException
     */
    public function saveIncrementalPopularity(QueryModel $query)
    {
        $adapter = $this->getConnection();
        $table = $this->getMainTable();
        $saveData = [
            'store_id' => $query->getStoreId(),
            'query_text' => Parser::parseSearchText($query->getQueryText()),
            'popularity' => 1,
        ];
        $updateData = [
            'popularity' => new \Zend_Db_Expr('`popularity` + 1'),
        ];
        $adapter->insertOnDuplicate($table, $saveData, $updateData);
    }

    /**
     * Save query with number of results
     *
     * @param QueryModel $query
     * @return void
     *
     * @throws LocalizedException
     */
    public function saveNumResults(QueryModel $query)
    {
        $adapter = $this->getConnection();
        $table = $this->getMainTable();
        $numResults = $query->getNumResults();
        $saveData = [
            'store_id' => $query->getStoreId(),
            'query_text' => Parser::parseSearchText($query->getQueryText()),
            'num_results' => $numResults,
        ];
        $updateData = ['num_results' => $numResults];
        $adapter->insertOnDuplicate($table, $saveData, $updateData);
    }
}