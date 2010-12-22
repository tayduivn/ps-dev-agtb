#!/usr/bin/env ruby

files=Dir.glob("*.xml")
puts files
files.each do |filelist|     
      content=IO.readlines(filelist)
      file=File.open(filelist,'w')
      content.each do |line|                       
		file.puts line.gsub(/scripts\/sugarcrm\/modules\/lib/i,'scripts/sugarcrm/modules/lib')
      end      
  end