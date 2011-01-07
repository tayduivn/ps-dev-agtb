#!/usr/bin/env ruby

files=Dir.glob("*.xml")
puts files
files.each do |filelist|     
      content=IO.readlines(filelist)
      file=File.open(filelist,'w')
      content.each do |line|                       
		file.puts line.gsub(/scripts\/sugarcrm_60\/csvs\/employees/i,'scripts/sugarcrm_60/csvs/employees')
      end      
  end
