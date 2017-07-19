set pdbFile /home/lcodo/Documents/pdbs/ATP-1mv5-empty-optA-tit.pdb
set lig ATP
set ca 1mv5

mol load pdb $pdbFile

set out [open /home/lcodo/Documents/pdbs/ATP-1mv5-empty-optA-tit.dist w]
set ang [open /home/lcodo/Documents/pdbs/ATP-1mv5-empty-optA-tit.ang w]

set wats [atomselect 0 "waters and name O"]

if { [$wats num] == 0} {
        puts "ERROR: No waters found in $pdbFile"
        exit
}

## Computing distance wat-prot 

foreach oxy [$wats get { resid }] {
  
  puts "OXY $oxy"
 
  set res [atomselect 0 "waters and resid $oxy"]
	puts "ok"
  set all [atomselect 0 "all not (waters and resid $oxy)"]
	puts "ok"
  set tot [atomselect 0 "all"]
	puts "ok"

  # Computing beta angle and ASA
  set catal [linsert [lindex [$res list] 1] 1 [lindex [$res list] 0] [lindex [$res list] 2]]
  puts $ang "$lig,$ca,$oxy,[measure angle $catal],[measure sasa 1.4 $tot -points pts -samples 150 -restrict $res]"

  # Looking for water contacts
  set conts [measure contacts 5 $res $all]

  set wat   [lindex $conts 0]
  set atms2 [lindex $conts 1]

  # Looking for "bonded" waters - dist < 1.2 A
  set bonds [$res getbonds]
  set ids   [$res get { index } ]


  for {set i 0 } {$i < 3} {incr i} {
         set id       [lindex $ids $i]
         set id_bonds [lindex $bonds $i]
         set sel_id   [atomselect 0 "index $id"]

         foreach at2 $id_bonds {
         	set sel_at2 [atomselect 0 "index $at2"]
                puts "  analizing $at2"

		if { [$sel_id get { resid }] != [$sel_at2 get { resid }] } {
                        puts "between $id (  [$sel_id get { resid }] ) and $at2 ( [$sel_at2 get { resid }] ) exist illegal bondd"
                        lappend wat $id
                        lappend atms2 $at2
                 }
        }
  }

  # Selecting H bonds from water contacts
  set len [llength $atms2]
  for {set i 0 } {$i < $len} {incr i} {

        set Hang 0
        set at1 [lindex $wat $i]
        set sel1 [atomselect 0 "index $at1"]
        set A [lindex [$sel1 get { x y z }] 0]
        set at2 [lindex $atms2 $i]
        set sel2 [atomselect 0 "index $at2"]
        set B [lindex [$sel2 get { x y z }] 0]

        set dist [vecdist $A $B]

  	puts "for OXY $oxy at1 $at1 [$sel1 get {resid }] [$sel1 get { type }] - at2 $at2  [$sel2 get {resid }] [$sel2 get { type }] ------ dist $dist"

  }
}

close $out
close $ang
exit
