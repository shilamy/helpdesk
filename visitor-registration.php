<?php
// visitor-registration.php
$page_title = "Register Visitor";
require_once 'includes/header.php';

if (!canRegisterVisitors()) {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate required fields
        $required_fields = ['FullName', 'Gender', 'IDType', 'IDNumber', 'PurposeOfVisit'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            throw new Exception("Please fill in all required fields: " . implode(', ', $missing_fields));
        }

        // Generate badge number
        $badge_number = generateBadgeNumber();
        
        // Get host details from the form
        $host_contact_id = $_POST['HostContactID'] ?? '';
        $host_contact_name = $_POST['HostContactName'] ?? '';
        $host_department = $_POST['HostDepartment'] ?? '';
        $host_floor = $_POST['HostFloor'] ?? '';
        $host_caller_id = $_POST['HostCallerID'] ?? '';
        
        // Determine if we're using a contact or manual input
        if (!empty($host_contact_id) && $host_contact_id !== 'manual') {
            // Using a contact from the database
            $host_name = $host_contact_name;
        } else {
            // Using manual input
            $host_name = sanitize($_POST['HostName'] ?? '');
            $host_contact_name = $host_name; // Use manual input for contact name
            $host_department = ''; // Clear department for manual entries
            $host_floor = ''; // Clear floor for manual entries
            $host_caller_id = ''; // Clear caller ID for manual entries
        }
        
        // Prepare the SQL query
        $query = "INSERT INTO visitors 
                 (FullName, Gender, PWDStatus, IDType, IDNumber, PhoneNumber, Organization, 
                  PurposeOfVisit, HostName, HostContactName, HostDepartment, HostFloor, HostCallerID, HostAvailable, VisitorMessage, BadgeNumber, HasLuggage, LuggageNumber, 
                  AdmittingOfficer, CheckInTime) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($query);
        
        // Execute with parameters
        $result = $stmt->execute([
            sanitize($_POST['FullName']),
            $_POST['Gender'],
            isset($_POST['PWDStatus']) ? 1 : 0,
            $_POST['IDType'],
            sanitize($_POST['IDNumber']),
            sanitize($_POST['PhoneNumber'] ?? ''),
            sanitize($_POST['Organization'] ?? ''), // Visitor's Organization
            sanitize($_POST['PurposeOfVisit']),
            $host_name,
            $host_contact_name,
            $host_department, // Host's Department
            $host_floor,      // Host's Floor
            $host_caller_id,  // Host's Caller ID
            $_POST['HostAvailable'] ?? 1,
            sanitize($_POST['VisitorMessage'] ?? ''),
            $badge_number,
            isset($_POST['HasLuggage']) ? 1 : 0,
            isset($_POST['HasLuggage']) && !empty($_POST['LuggageNumber']) ? sanitize($_POST['LuggageNumber']) : NULL,
            $_SESSION['user_name']
        ]);
        
        if ($result) {
            $visitor_id = $pdo->lastInsertId();
            
            // Log activity
            logActivity($_SESSION['user_id'], 'Visitor Registration', 
                       "Registered visitor: " . sanitize($_POST['FullName']));
            
            // Success message
            $message = "Visitor registered successfully! Redirecting to badge...";
            
            // Redirect to generate badge
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'generate-badge.php?id=" . $visitor_id . "&success=1';
                }, 2000);
            </script>";
            
        } else {
            throw new Exception("Database insertion failed.");
        }
        
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
        error_log("Visitor Registration Error: " . $e->getMessage());
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="container">
    <div class="main-content">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Quick Actions</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="visitor-registration.php" class="active"><i class="fas fa-user-plus"></i> Register Visitor</a></li>
                <li><a href="visitor-management.php"><i class="fas fa-list"></i> Active Visitors</a></li>
                <li><a href="visitor-management.php?filter=history"><i class="fas fa-history"></i> Visitor History</a></li>
            </ul>
        </aside>
        
        <!-- Content Area -->
        <main class="content">
            <h1 style="margin-bottom: 20px; color: var(--primary-brown);">Register New Visitor</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" id="visitorForm">
                    <!-- Visitor Information Section -->
                    <div class="form-section" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid var(--primary-brown);">
                        <h3 style="color: var(--primary-brown); margin-bottom: 15px;">
                            <i class="fas fa-user"></i> Visitor Information
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="required">Full Name</label>
                                <input type="text" name="FullName" required 
                                       value="<?php echo isset($_POST['FullName']) ? $_POST['FullName'] : ''; ?>"
                                       placeholder="Enter visitor's full name">
                            </div>
                            <div class="form-group">
                                <label class="required">Gender</label>
                                <select name="Gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo (isset($_POST['Gender']) && $_POST['Gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo (isset($_POST['Gender']) && $_POST['Gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo (isset($_POST['Gender']) && $_POST['Gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="required">ID Type</label>
                                <select name="IDType" required>
                                    <option value="">Select ID Type</option>
                                    <option value="ID" <?php echo (isset($_POST['IDType']) && $_POST['IDType'] == 'ID') ? 'selected' : ''; ?>>National ID</option>
                                    <option value="Passport" <?php echo (isset($_POST['IDType']) && $_POST['IDType'] == 'Passport') ? 'selected' : ''; ?>>Passport</option>
                                    <option value="Driving License" <?php echo (isset($_POST['IDType']) && $_POST['IDType'] == 'Driving License') ? 'selected' : ''; ?>>Driving License</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="required">ID Number</label>
                                <input type="text" name="IDNumber" required 
                                       value="<?php echo isset($_POST['IDNumber']) ? $_POST['IDNumber'] : ''; ?>"
                                       placeholder="Enter ID/Passport number">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="PhoneNumber" 
                                       value="<?php echo isset($_POST['PhoneNumber']) ? $_POST['PhoneNumber'] : ''; ?>"
                                       placeholder="Enter phone number">
                            </div>
                            <div class="form-group">
                                <label>Visitor Organization</label>
                                <input type="text" name="Organization" 
                                       value="<?php echo isset($_POST['Organization']) ? $_POST['Organization'] : ''; ?>"
                                       placeholder="Enter visitor's organization/company">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="required">Purpose of Visit</label>
                            <textarea name="PurposeOfVisit" rows="3" required 
                                      placeholder="Enter purpose of visit"><?php echo isset($_POST['PurposeOfVisit']) ? $_POST['PurposeOfVisit'] : ''; ?></textarea>
                        </div>
                    </div>

                    <!-- Host Information Section -->
                    <div class="form-section" style="margin-bottom: 30px;">
                        <h3 style="color: var(--primary-brown); margin-bottom: 15px;">
                            <i class="fas fa-user-tie"></i> Host Information
                        </h3>
                        
                        <div class="form-group">
                            <label>Host Name (Search or Type Manually)</label>
                            <div id="hostSearchContainer">
                                <input type="text" id="hostSearch" name="HostName" 
                                       value="<?php echo isset($_POST['HostName']) ? $_POST['HostName'] : ''; ?>"
                                       placeholder="Start typing to search KNBS staff or type manually..."
                                       autocomplete="off">
                                <div id="hostSearchResults" class="search-results"></div>
                            </div>
                            <small style="color: #666; margin-top: 5px; display: block;">
                                <i class="fas fa-info-circle"></i> Search by name, department, or floor. Select from suggestions or continue typing for manual entry.
                            </small>
                        </div>

                        <!-- Hidden fields to store the actual host details -->
                        <input type="hidden" name="HostContactID" id="HostContactID" value="">
                        <input type="hidden" name="HostContactName" id="HostContactName" value="">
                        <input type="hidden" name="HostDepartment" id="HostDepartment" value="">
                        <input type="hidden" name="HostFloor" id="HostFloor" value="">
                        <input type="hidden" name="HostCallerID" id="HostCallerID" value="">

                        <!-- Host Details Display -->
                        <div id="hostDetails" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid var(--primary-brown);">
                            <h4 style="margin-bottom: 10px; color: var(--primary-brown);">
                                <i class="fas fa-info-circle"></i> Host Details
                            </h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                                <div>
                                    <strong>Host Name:</strong>
                                    <span id="displayContactName">-</span>
                                </div>
                                <div>
                                    <strong>Department:</strong>
                                    <span id="displayDepartment">-</span>
                                </div>
                                <div>
                                    <strong>Floor:</strong>
                                    <span id="displayFloor">-</span>
                                </div>
                                <div>
                                    <strong>Caller ID:</strong>
                                    <span id="displayCallerID">-</span>
                                </div>
                            </div>
                            <div id="manualEntryNote" style="display: none; margin-top: 10px; padding: 8px; background: #fff3cd; border-radius: 4px; border-left: 4px solid #ffc107;">
                                <small><i class="fas fa-exclamation-triangle"></i> <strong>Manual Entry:</strong> Host details will be saved as entered above.</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Host Available?</label>
                                <select name="HostAvailable" id="HostAvailable">
                                    <option value="1" selected>Yes</option>
                                    <option value="0">No - Host Not Available</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group" id="visitorMessageField" style="display: none;">
                            <label>Message for Host</label>
                            <textarea name="VisitorMessage" rows="3" placeholder="Leave a message for the host if they are not available"><?php echo isset($_POST['VisitorMessage']) ? $_POST['VisitorMessage'] : ''; ?></textarea>
                        </div>
                    </div>

                    <!-- Additional Information Section -->
                    <div class="form-section">
                        <h3 style="color: var(--primary-brown); margin-bottom: 15px;">
                            <i class="fas fa-info-circle"></i> Additional Information
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group checkbox-group">
                                <input type="checkbox" name="PWDStatus" id="PWDStatus" value="1" <?php echo (isset($_POST['PWDStatus'])) ? 'checked' : ''; ?>>
                                <label for="PWDStatus">Person with Disability</label>
                            </div>
                            <div class="form-group checkbox-group">
                                <input type="checkbox" name="HasLuggage" id="HasLuggage" value="1" <?php echo (isset($_POST['HasLuggage'])) ? 'checked' : ''; ?>>
                                <label for="HasLuggage">Has Luggage</label>
                            </div>
                        </div>
                        
                        <div class="form-group" id="luggageNumberField" style="display: <?php echo (isset($_POST['HasLuggage'])) ? 'block' : 'none'; ?>;">
                            <label>Luggage Number</label>
                            <input type="text" name="LuggageNumber" 
                                   value="<?php echo isset($_POST['LuggageNumber']) ? $_POST['LuggageNumber'] : ''; ?>"
                                   placeholder="Enter luggage tag number">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-size: 1.1rem; margin-top: 20px;">
                        <i class="fas fa-id-card"></i> Register Visitor & Print Badge
                    </button>
                </form>
            </div>
            
            <!-- Badge Preview -->
            <div style="margin-top: 40px; text-align: center;">
                <h3 style="margin-bottom: 15px; color: var(--primary-brown);">Visitor Badge Preview</h3>
                <div class="badge-preview">
                    <div class="badge-header">
                        <h3>VISITOR PASS</h3>
                        <div>KENYA NATIONAL BUREAU OF STATISTICS</div>
                    </div>
                    <div class="badge-name" id="previewName">Visitor Name</div>
                    <div class="badge-organization" id="previewOrganization">Organization</div>
                    <div class="badge-number"><?php echo generateBadgeNumber(); ?></div>
                    <div class="badge-date"><?php echo date('F j, Y'); ?></div>
                    <div style="margin-top: 15px; font-size: 0.9rem; color: var(--text-muted);">
                        Valid for today only • Must be returned at exit
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Host data from PHP
const hostContacts = <?php echo json_encode(getAllContacts()); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const luggageCheckbox = document.getElementById('HasLuggage');
    const luggageField = document.getElementById('luggageNumberField');
    const hostAvailableSelect = document.getElementById('HostAvailable');
    const messageField = document.getElementById('visitorMessageField');
    
    if (luggageCheckbox && luggageField) {
        luggageCheckbox.addEventListener('change', function() {
            luggageField.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) {
                document.querySelector('input[name="LuggageNumber"]').value = '';
            }
        });
    }
    
    if (hostAvailableSelect && messageField) {
        hostAvailableSelect.addEventListener('change', function() {
            messageField.style.display = this.value === '0' ? 'block' : 'none';
        });
    }
    
    // Real-time badge preview
    const nameInput = document.querySelector('input[name="FullName"]');
    const orgInput = document.querySelector('input[name="Organization"]');
    const previewName = document.getElementById('previewName');
    const previewOrg = document.getElementById('previewOrganization');
    
    if (nameInput && previewName) {
        nameInput.addEventListener('input', function() {
            previewName.textContent = this.value || 'Visitor Name';
        });
    }
    
    if (orgInput && previewOrg) {
        orgInput.addEventListener('input', function() {
            previewOrg.textContent = this.value || 'Organization';
        });
    }

    // Form validation
    const form = document.getElementById('visitorForm');
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = '#dc3545';
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields marked with *');
        }
    });

    // Initialize host search functionality
    initializeHostSearch();
});

// Host Search Functionality
function initializeHostSearch() {
    const searchInput = document.getElementById('hostSearch');
    const searchResults = document.getElementById('hostSearchResults');
    const hostDetails = document.getElementById('hostDetails');
    const manualEntryNote = document.getElementById('manualEntryNote');

    let selectedContact = null;
    let searchTimeout = null;

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Hide results if search term is too short
        if (searchTerm.length < 2) {
            searchResults.style.display = 'none';
            hideHostDetails();
            return;
        }
        
        // Debounce search
        searchTimeout = setTimeout(() => {
            performSearch(searchTerm);
        }, 300);
    });

    searchInput.addEventListener('focus', function() {
        const searchTerm = this.value.trim();
        if (searchTerm.length >= 2) {
            performSearch(searchTerm);
        }
    });

    searchInput.addEventListener('blur', function() {
        // Delay hiding results to allow clicking on them
        setTimeout(() => {
            searchResults.style.display = 'none';
        }, 200);
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            searchResults.style.display = 'none';
        }
    });

    function performSearch(searchTerm) {
        const results = hostContacts.filter(contact => {
            const contactName = contact.contact_name?.toLowerCase() || '';
            const department = contact.department?.toLowerCase() || '';
            const floor = contact.floor?.toLowerCase() || '';
            const callerId = contact.Caller_ID?.toLowerCase() || '';
            
            return contactName.includes(searchTerm.toLowerCase()) ||
                   department.includes(searchTerm.toLowerCase()) ||
                   floor.includes(searchTerm.toLowerCase()) ||
                   callerId.includes(searchTerm.toLowerCase());
        });

        displaySearchResults(results.slice(0, 10)); // Show top 10 results
    }

    function displaySearchResults(results) {
        searchResults.innerHTML = '';
        
        if (results.length === 0) {
            const noResult = document.createElement('div');
            noResult.className = 'search-result-item';
            noResult.innerHTML = `
                <div style="padding: 10px; color: #666; text-align: center;">
                    <i class="fas fa-search"></i> No staff found matching your search
                </div>
            `;
            searchResults.appendChild(noResult);
        } else {
            results.forEach(contact => {
                const resultItem = document.createElement('div');
                resultItem.className = 'search-result-item';
                resultItem.innerHTML = `
                    <div class="contact-name">${contact.contact_name}</div>
                    <div class="contact-details">
                        ${contact.department} • Floor ${contact.floor} • ${contact.Caller_ID}
                    </div>
                `;
                
                resultItem.addEventListener('mousedown', (e) => {
                    e.preventDefault(); // Prevent input blur
                    selectContact(contact);
                });
                
                searchResults.appendChild(resultItem);
            });
        }
        
        searchResults.style.display = 'block';
    }

    function selectContact(contact) {
        selectedContact = contact;
        
        // Update the search input
        searchInput.value = contact.contact_name;
        
        // Update hidden fields
        document.getElementById('HostContactID').value = contact.id;
        document.getElementById('HostContactName').value = contact.contact_name;
        document.getElementById('HostDepartment').value = contact.department;
        document.getElementById('HostFloor').value = contact.floor;
        document.getElementById('HostCallerID').value = contact.Caller_ID;
        
        // Show host details
        showHostDetails(contact);
        
        // Hide search results
        searchResults.style.display = 'none';
        
        // Hide manual entry note
        manualEntryNote.style.display = 'none';
    }

    function showHostDetails(contact) {
        document.getElementById('displayContactName').textContent = contact.contact_name;
        document.getElementById('displayDepartment').textContent = contact.department;
        document.getElementById('displayFloor').textContent = contact.floor;
        document.getElementById('displayCallerID').textContent = contact.Caller_ID;
        hostDetails.style.display = 'block';
    }

    function hideHostDetails() {
        // Clear hidden fields
        document.getElementById('HostContactID').value = '';
        document.getElementById('HostContactName').value = '';
        document.getElementById('HostDepartment').value = '';
        document.getElementById('HostFloor').value = '';
        document.getElementById('HostCallerID').value = '';
        
        // Hide host details
        hostDetails.style.display = 'none';
        manualEntryNote.style.display = 'none';
        selectedContact = null;
    }

    // Detect manual entry
    searchInput.addEventListener('change', function() {
        const currentValue = this.value.trim();
        
        if (currentValue && (!selectedContact || selectedContact.contact_name !== currentValue)) {
            // This is a manual entry
            document.getElementById('HostContactID').value = 'manual';
            document.getElementById('HostContactName').value = currentValue;
            document.getElementById('HostDepartment').value = '';
            document.getElementById('HostFloor').value = '';
            document.getElementById('HostCallerID').value = '';
            
            // Show host details for manual entry
            document.getElementById('displayContactName').textContent = currentValue;
            document.getElementById('displayDepartment').textContent = 'Not specified';
            document.getElementById('displayFloor').textContent = 'Not specified';
            document.getElementById('displayCallerID').textContent = 'Not specified';
            
            hostDetails.style.display = 'block';
            manualEntryNote.style.display = 'block';
        }
    });

    // Initialize with any existing value
    const initialHostName = searchInput.value;
    if (initialHostName) {
        // Try to find matching contact
        const matchingContact = hostContacts.find(contact => 
            contact.contact_name === initialHostName
        );
        
        if (matchingContact) {
            selectContact(matchingContact);
        } else {
            // It's a manual entry
            document.getElementById('HostContactID').value = 'manual';
            document.getElementById('HostContactName').value = initialHostName;
            hostDetails.style.display = 'block';
            manualEntryNote.style.display = 'block';
        }
    }
}
</script>

<style>
/* Host Search Styles */
#hostSearchContainer {
    position: relative;
}

#hostSearch {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    font-size: 1rem;
    background: white;
}

#hostSearch:focus {
    border-color: var(--primary-brown);
    outline: none;
    box-shadow: 0 0 5px rgba(139, 69, 19, 0.3);
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.search-result-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background-color 0.2s;
}

.search-result-item:hover {
    background-color: #f8f9fa;
}

.search-result-item:last-child {
    border-bottom: none;
}

.contact-name {
    font-weight: 600;
    color: var(--primary-brown);
    margin-bottom: 4px;
}

.contact-details {
    font-size: 0.85rem;
    color: #666;
}

/* Form section styles */
.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
}

.form-section:not(:last-child) {
    border-bottom: 2px solid var(--border-color);
}

.form-section h3 {
    background: linear-gradient(135deg, var(--primary-brown), #A0522D);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    padding-bottom: 5px;
    border-bottom: 2px solid var(--primary-brown);
}

/* Host details styles */
#hostDetails {
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.host-detail-item {
    margin-bottom: 8px;
    padding: 5px 0;
}

.host-detail-label {
    font-weight: 600;
    color: var(--primary-brown);
}

/* Manual entry note */
#manualEntryNote {
    transition: all 0.3s ease;
}

/* Responsive design */
@media (max-width: 768px) {
    .search-results {
        position: fixed;
        top: auto;
        left: 10px;
        right: 10px;
        max-height: 200px;
    }
    
    #hostSearch {
        font-size: 16px; /* Prevent zoom on iOS */
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>