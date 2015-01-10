<?php
class Calendar extends DB_Connect{
    private $_useDate;
    //month
    private $_m;
    //year
    private $_yr;
    //days in month
    private $_daysInMonth;
    //day of week month starts from
    private $_startDay;
    
    public function __construct($dbo = NULL, $useDate= NULL) {
        parent::__construct($dbo);
    
        if(isset($useDate)){
            $this->_useDate=$useDate;
        }
 else {
     $this->_useDate=  date('Y-m-d H:i:s');
 }
 
 $ts=  strtotime($this->_useDate);
 $this->_m = date('m', $ts);
 $this->_yr = date('Y',$ts);
 
 //how many days in month
    $this->_daysInMonth = cal_days_in_month(
            CAL_GREGORIAN, 
            $this->_m, 
            $this->_yr
            );
 
    $ts = mktime(0, 0, 0, $this->_m, 1, $this->_yr);
    $this->_startDay = date('w', $ts);
 
    }
    
    private function _loadEventData($id=NULL){
        $sql = "SELECT * FROM `eventsabout`";
        
        if(!empty($id)){
            $sql .= "WHERE `event_id`=:id LIMIT 1";
        }
        else{
            $start_ts = mktime(0, 0, 0, $this->_m, 1, $this->_yr);
            $end_ts = mktime(23, 59, 59, $this->_m+1, 0, $this->_yr);
            $start_date = date('Y-m-d H:i:s', $start_ts);
            $end_date = date('Y-m-d H:i:s', $end_ts);
        
            $sql .= "WHERE `event_start` BETWEEN '$start_date' AND '$end_date' ORDER BY `event_start`";
            }
         try{
             $stmt -> $this->db->prepare($sql);
             
             if (!empty($id)){
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $results;
            }
        catch(Exception $e){
            die($e->getMessage());
        }
    }
    
    private function _createEventObject() {
        $arr = $this->_loadEventData();
        
        $events = array();
        
        foreach ($arr as $event){
            $day = date('j',  strtotime($event['event_start']));
            
        try{
            $events[day][] = new Event($event); 
        }
        catch(Exception $e){
            die($e->getMessage());
        }
        }
        return $events;
    }
    
    public function buildCalendar(){
        $cal_month = date('F Y',  strtotime($this->_useDate));
        $weekdays = array('Sun','Mon','Tue','Wed','Thurs','Fri','Sat');
        
        $html = "\n\t<h2>$cal_month</h2>";
        for ( $d=0, $labels=NULL; $d<7; ++$d )
        {
        $labels .= "\n\t\t<li>" . $weekdays[$d] . "</li>";
        }
        
        $html .= "\n\t<ul class=\"weekdays\">". $labels . "\n\t</ul>";
    
        $html .= "\n\t<ul>"; // Start a new unordered list
        for ( $i=1, $c=1, $t=date('j'), $m=date('m'), $y=date('Y');$c<=$this->_daysInMonth; ++$i )
        {
            $class = $i<=$this->_startDay?'fill':NULL;
            
            if($c==$t && $m==$this->_m && $y==$this->_yr){
                $class = "today";
            }
            $ls = sprintf("\n\t\t<li class=\"%s\">", $class);
            $le = "\n\t\t</li>";
            
            if ( $this->_startDay<$i && $this->_daysInMonth>=$c){
            $date = sprintf("\n\t\t\t<strong>%02d</strong>",$c++);
            }
            else { $date="&nbsp;"; }
            /*
            * If the current day is a Saturday, wrap to the next row
            */
            $wrap = $i!=0 && $i%7==0 ? "\n\t</ul>\n\t<ul>" : NULL;
 
            $html .= $ls . $date . $le . $wrap;
            
            while ($i%7!=1){
                $html .= "\n\t\t<li class=\"fill\">&nbsp;</li>";
            ++$i;
            }
            $html .= "\n\t</ul>\n\n";
        }
        
        return $html;
        }
        
    private function _loadEventById($id){
 /*
 * If no ID is passed, return NULL
 */
 if ( empty($id) )
 {
 return NULL;
 }
 /*
 * Load the events info array
 */
 $event = $this->_loadEventData($id);
 
 if ( isset($event[0]) )
 {
 return new Event($event[0]);
 }
 else
 {
 return NULL;
 }
 }

    public function displayEvent($id){
 /*
 * Make sure an ID was passed
 */
 if ( empty($id) ) { return NULL; }
 /*
 * Make sure the ID is an integer
 */
 $id = preg_replace('/[^0-9]/', '', $id);
 /*
 * Load the event data from the DB
 */
 $event = $this->_loadEventById($id);
 /*
 * Generate strings for the date, start, and end time
 */
 $ts = strtotime($event->start);
 $date = date('F d, Y', $ts);
 $start = date('g:ia', $ts);
 $end = date('g:ia', strtotime($event->end));
 /*
 * Generate and return the markup
 */
 return "<h2>$event->title</h2>"
 . "\n\t<p class=\"dates\">$date, $start&mdash;$end</p>"
 . "\n\t<p>$event->description</p>";
 }
    }
