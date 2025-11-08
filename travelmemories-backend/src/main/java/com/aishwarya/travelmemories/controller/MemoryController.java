package com.aishwarya.travelmemories.controller;
import com.aishwarya.travelmemories.model.Memory;
import com.aishwarya.travelmemories.repository.MemoryRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;
import java.util.List;
@RestController
@RequestMapping("/api/memories")
public class MemoryController {
    @Autowired
    private MemoryRepository memoryRepository;
    @GetMapping
    public List<Memory> getAllMemories() { return memoryRepository.findAll(); }
    @PostMapping
    public Memory createMemory(@RequestBody Memory memory) { return memoryRepository.save(memory); }
}