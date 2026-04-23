// ─── SHARED CROSS-OFF FEATURE FUNCTIONALITY ───
// Used by Mood Lifting and Mind Reset pages

// Configuration - override these per page
const CROSS_OFF_CONFIG = {
  storageKey: 'cross_off_progress', // Will be overridden per page
  itemSelector: '.cross-off-item',
  counterSelector: '.cross-off-counter',
  resetButtonSelector: '.cross-off-reset-btn',
  onAllDone: null, // Optional callback when all items are crossed off
  onItemToggle: null // Optional callback when an item is toggled
};

// Toggle cross-off state
function toggleCrossOff(element, pageKey) {
  element.classList.toggle('crossed-off');
  
  // Save progress with page-specific key
  saveCrossOffProgress(pageKey);
  
  // Update counter
  updateCrossOffCounter(pageKey);
  
  // Trigger callback if exists
  const config = getPageConfig(pageKey);
  if (config && config.onItemToggle) {
    config.onItemToggle(element);
  }
  
  // Check if all done
  checkAllDone(pageKey);
}

// Save progress to localStorage
function saveCrossOffProgress(pageKey) {
  const items = document.querySelectorAll('.cross-off-item');
  const progress = [];
  
  items.forEach((item, index) => {
    progress.push({
      index: index,
      crossedOff: item.classList.contains('crossed-off'),
      text: item.querySelector('.cross-off-text')?.textContent || ''
    });
  });
  
  localStorage.setItem(`cross_off_${pageKey}`, JSON.stringify(progress));
}

// Load progress from localStorage
function loadCrossOffProgress(pageKey) {
  const saved = localStorage.getItem(`cross_off_${pageKey}`);
  if (!saved) return false;
  
  try {
    const progress = JSON.parse(saved);
    const items = document.querySelectorAll('.cross-off-item');
    
    progress.forEach(item => {
      if (item.crossedOff && items[item.index]) {
        items[item.index].classList.add('crossed-off');
      }
    });
    
    return true;
  } catch (e) {
    console.error('Error loading saved progress:', e);
    return false;
  }
}

// Update counter showing progress
function updateCrossOffCounter(pageKey) {
  const items = document.querySelectorAll('.cross-off-item');
  const crossedCount = Array.from(items).filter(item => 
    item.classList.contains('crossed-off')
  ).length;
  const totalCount = items.length;
  
  // Find or create counter
  let counter = document.querySelector(`.cross-off-counter[data-page="${pageKey}"]`);
  if (!counter) {
    counter = document.querySelector('.cross-off-counter');
  }
  
  if (!counter) return;
  
  counter.textContent = `${crossedCount} / ${totalCount} completed`;
  counter.setAttribute('data-page', pageKey);
  
  // Style when all done
  if (crossedCount === totalCount && totalCount > 0) {
    counter.innerHTML = `✨ All done! Great job! ✨`;
    counter.classList.add('all-done');
    
    // Trigger callback if exists
    const config = getPageConfig(pageKey);
    if (config && config.onAllDone) {
      config.onAllDone();
    }
  } else {
    counter.classList.remove('all-done');
  }
}

// Check if all items are crossed off
function checkAllDone(pageKey) {
  const items = document.querySelectorAll('.cross-off-item');
  const crossedCount = Array.from(items).filter(item => 
    item.classList.contains('crossed-off')
  ).length;
  
  if (crossedCount === items.length && items.length > 0) {
    // Optional: Add confetti or celebration effect
    console.log(`🎉 All items completed on ${pageKey}!`);
  }
}

// Reset all items for a page
function resetCrossOffProgress(pageKey) {
  const items = document.querySelectorAll('.cross-off-item');
  items.forEach(item => {
    item.classList.remove('crossed-off');
  });
  
  // Clear saved progress
  localStorage.removeItem(`cross_off_${pageKey}`);
  
  // Update counter
  updateCrossOffCounter(pageKey);
}

// Get page-specific configuration
function getPageConfig(pageKey) {
  // This can be extended based on the page
  const configs = {
    'mood-lifting': {
      onAllDone: () => {
        console.log('All mood lifting thoughts reflected!');
      }
    },
    'mind-reset': {
      onAllDone: () => {
        console.log('All mind reset activities completed!');
      }
    }
  };
  
  return configs[pageKey] || {};
}

// Initialize cross-off feature for a page
function initCrossOff(pageKey, options = {}) {
  // Set up click handlers for all items
  const items = document.querySelectorAll('.cross-off-item');
  items.forEach(item => {
    // Remove existing listener to avoid duplicates
    item.removeEventListener('click', item._clickHandler);
    
    // Create new handler
    item._clickHandler = () => toggleCrossOff(item, pageKey);
    item.addEventListener('click', item._clickHandler);
  });
  
  // Load saved progress
  loadCrossOffProgress(pageKey);
  
  // Create counter if it doesn't exist
  if (!document.querySelector('.cross-off-counter')) {
    const header = document.querySelector('.ml-header, .mr-header, .page-header');
    if (header) {
      const counter = document.createElement('div');
      counter.className = 'cross-off-counter';
      counter.setAttribute('data-page', pageKey);
      header.appendChild(counter);
    }
  }
  
  // Update counter
  updateCrossOffCounter(pageKey);
  
  // Set up reset button if exists
  const resetBtn = document.querySelector('.cross-off-reset-btn');
  if (resetBtn) {
    resetBtn.removeEventListener('click', resetBtn._clickHandler);
    resetBtn._clickHandler = () => resetCrossOffProgress(pageKey);
    resetBtn.addEventListener('click', resetBtn._clickHandler);
  }
  
  // Add keyboard shortcut (press 'r' to reset)
  document.addEventListener('keydown', (e) => {
    if (e.key === 'r' || e.key === 'R') {
      // Only reset if not typing in an input
      if (document.activeElement.tagName !== 'INPUT' && 
          document.activeElement.tagName !== 'TEXTAREA') {
        resetCrossOffProgress(pageKey);
      }
    }
  });
}

// Export functions for global use
window.toggleCrossOff = toggleCrossOff;
window.resetCrossOffProgress = resetCrossOffProgress;
window.initCrossOff = initCrossOff;