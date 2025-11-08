package com.aishwarya.travelmemories.controller;

import com.aishwarya.travelmemories.model.Memory;
import com.aishwarya.travelmemories.repository.MemoryRepository;
import com.aishwarya.travelmemories.service.S3Service;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.format.annotation.DateTimeFormat;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import java.util.Date;
import java.util.List;

@RestController
@RequestMapping("/api/memories")
@CrossOrigin(origins = "*")
public class MemoryController {

    @Autowired
    private MemoryRepository memoryRepository;

    @Autowired
    private S3Service s3Service;

    @GetMapping
    public List<Memory> getAllMemories() {
        return memoryRepository.findAll();
    }

    @PostMapping
    public ResponseEntity<?> createMemory(
            @RequestParam("title") String title,
            @RequestParam("location") String location,
            @RequestParam("travel_date") @DateTimeFormat(pattern = "yyyy-MM-dd") Date travelDate,
            @RequestParam("description") String description,
            @RequestParam("photo") MultipartFile photo) {
        
        try {
            // Upload image to S3
            String photoUrl = s3Service.uploadFile(photo);

            // Create and save memory
            Memory memory = new Memory();
            memory.setTitle(title);
            memory.setLocation(location);
            memory.setTravel_date(travelDate);
            memory.setDescription(description);
            memory.setPhoto_url(photoUrl);

            Memory savedMemory = memoryRepository.save(memory);
            return ResponseEntity.ok(savedMemory);
        } catch (Exception e) {
            return ResponseEntity.badRequest().body("Error: " + e.getMessage());
        }
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<?> deleteMemory(@PathVariable Long id) {
        memoryRepository.deleteById(id);
        return ResponseEntity.ok("Memory deleted successfully");
    }
}