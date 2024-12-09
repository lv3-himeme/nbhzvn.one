<?php
class Nbhzvn_Game {
    public $id;
    public $timestamp;
    public $name;
    public $links;
    public $image;
    public $screenshots;
    public $description;
    public $engine;
    public $tags;
    public $release_date;
    public $author;
    public $language;
    public $translator;
    public $uploader;
    public $status;
    public $views;
    public $views_today;
    public $updated_date;
    public $downloads;
    public $supported_os;
    public $is_featured;
    public $approved;

    function __construct($id) {
        $result = db_query('SELECT * FROM `nbhzvn_games` WHERE id = ?', $id);
        while ($row = $result->fetch_object()) {
            $this->id = $row->id;
            $this->timestamp = $row->timestamp;
            $this->name = $row->name;
            $this->links = json_decode($row->links);
            $this->image = $row->image;
            $this->screenshots = json_decode($row->screenshots);
            $this->description = $row->description;
            $this->engine = $row->engine;
            $this->tags = $row->tags;
            $this->release_date = $row->release_date;
            $this->author = $row->author;
            $this->translator = $row->translator;
            $this->uploader = $row->uploader;
            $this->status = $row->status;
            $this->views = $row->views;
            $this->views_today = $row->views_today;
            $this->updated_date = $row->updated_date;
            $this->downloads = $row->downloads;
            $this->supported_os = $row->supported_os;
            $this->is_featured = $row->is_featured;
            $this->approved = $row->is_approved;
        }
    }
}
?>