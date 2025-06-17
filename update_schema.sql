-- First, drop foreign key constraint in attendance_records if it exists
ALTER TABLE attendance_records DROP FOREIGN KEY IF EXISTS fk_attendance_schedule;

-- Modify the schedules table to add the status column
ALTER TABLE schedules 
ADD COLUMN IF NOT EXISTS status ENUM('Present', 'Absent', 'DayOff', 'Ill', 'Justified') NOT NULL DEFAULT 'Absent' 
AFTER shift_id;

-- Add schedule_id column to attendance_records
ALTER TABLE attendance_records 
ADD COLUMN IF NOT EXISTS schedule_id INT(11) NULL AFTER employee_id;

-- Add foreign key constraint
ALTER TABLE attendance_records 
ADD CONSTRAINT fk_attendance_schedule 
FOREIGN KEY (schedule_id) REFERENCES schedules(schedule_id); 