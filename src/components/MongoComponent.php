<?php

namespace Api\Component;


use Phalcon\Di\Injectable;

/**
 * MongoDb CRUD
 */
class MongoComponent extends Injectable
{
    /**
     * returns all documents in the given collection
     *
     * @return void
     */
    public function read($collection, $data, $limit = 0, $project = [], $skip = 0)
    {
        try {
            // if project is set
            if (count($project) > 0) {
                $data = $this->mongo->$collection->find($data, ['projection' => $project, 'limit' => $limit, 'skip' => $skip]);
            } else {
                $data = $this->mongo->$collection->find($data, ['limit' => $limit, 'skip' => $skip]);
            }
            return $data->toArray();
        } catch (\Exception $e) {
            // echo $e->getMessage();
            return [];
        }
    }
    /**
     * inserts given data into given collection
     *
     * @param [type] $collection
     * @param [type] $data
     * @return inserted doc id
     */
    public function insert($collection, $data)
    {
        $data = $this->mongo->$collection->insertOne($data);
        return $data->getInsertedId();
    }
    public function aggregate($collection, $data)
    {
        $data = $this->mongo->$collection->aggregate($data);
        return $data->toArray();
    }
}
