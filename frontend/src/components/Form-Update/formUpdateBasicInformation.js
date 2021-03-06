import React, { Component } from 'react'
import * as API from '../../api';
import { connect } from 'react-redux';

class FormUpdateBasicInformation extends Component {
    componentDidMount(){
        API.getListSchool()
        .then(res=>{
            this.setState({
                listSchool: res.data.Schools,
                school_id: res.data.Schools[0].id
            })
        })
        .catch(err=>{
            console.log(err);
            return this.setState({
                error: "server error"
            })
        })
    }
    constructor(props) {
        super(props);
        let { account, name, birthday, gender, mssv, school_id, name_school } = this.props
        
        //console.log(API.getListHobby(account));

        this.state = {
            name: name ? name : '',
            birthday: birthday ? birthday : '',
            gender: gender ? gender : '',
            mssv: mssv ? mssv : '',
            classes: this.props.class ? this.props.class : '',
            listSchool: [],
            school_id: school_id ? school_id : '',
            name_school: name_school ? name_school : '',
            error:''
        }
    }

    renderSelectSchool = () => {
        let { listSchool } = this.state;
        console.log(listSchool);
        return listSchool.map((school, index) => {
            return <option key={school.id} value={index}>{school.name}</option>
        });
    }

    onChangeSchool = (e) => {
        let { listSchool } = this.state;
        let value = e.target.value;
        this.setState({
            name_school: listSchool[value].name,
            school_id: listSchool[value].id
        })
    }

    inputOnchange = (e) => {
        let target = e.target;
        this.setState({
            [target.name]: target.value
        })
    }

    onChangeGender = (e) => {
        this.setState({
            gender: e.target.value
        })
    }

    onEditBasicInformation = (e) => {
        e.preventDefault();
        let { name, birthday, gender, mssv, classes, school_id } = this.state;
        let { account,toggleEditForm, regetData } = this.props

        console.log(school_id);
        API.UpdateProfile(account,{
            name,
            birthday,
            gender,
            mssv,
            class: classes,
            school_id
        })
            .then(res => {
                if(res.status == 200){
                    alert('Edit successfully');
                    regetData(account);
                    toggleEditForm();
                }
            })
            .catch(err => {
                console.log(err);
                let errors = [];
                let message = '';
                if (err.response && err.response.data) {
                    message = err.response.data['message'];
                    errors = err.response.data.errors ? Object.values(err.response.data.errors)[0] : []
                }
                this.setState({
                    error: errors[0] ? errors[0] : (message ? message : 'username or password is incorrect')
                })
            })
    }

    render() {
        let { toggleEditForm } = this.props
        let { name, birthday, gender, mssv, classes, listHobby, error} = this.state;
        console.log(listHobby);
        return (
            <div>
                <div className="overview-box open" style={{ backgroundColor: '#00000000' }} id="bs-info-bx-form">
                    <div className="overview-edit">
                        <h3>Edit Basic Information</h3>
                        <form>
                            <h4>Fullname:</h4>
                            <input type="text" name="name" placeholder="Fullname" value={name} onChange={this.inputOnchange} />

                            <h4>Date of Birth:</h4>
                            <input type="date" name="birthday" placeholder="Birthday" value={birthday} onChange={this.inputOnchange} />

                            <h4>Gender:</h4>
                            <select value={gender} onChange={this.onChangeGender} style={{ paddingLeft: 15 + 'px' }}>
                                <option value='Nam'>Nam</option>
                                <option value='Nữ' >Nữ</option>
                                <option value="Khác">Khác</option>
                            </select>

                            <h4>MSSV:</h4>
                            <input type="text" name="mssv" placeholder="MSSV" value={mssv} onChange={this.inputOnchange} />

                            <h4>Class:</h4>
                            <input type="text" name="classes" placeholder="Class" value={classes} onChange={this.inputOnchange} />

                            <h4>School:</h4>
                            <select onChange={this.onChangeSchool} style={{ paddingLeft: 15 + 'px' }}>
                                {this.renderSelectSchool()}
                            </select>

                            <div style={{ color: 'red' }}>{error}</div>
                            <button onClick={this.onEditBasicInformation} className="save">Save</button>
                            <button onClick={toggleEditForm} className="cancel">Cancel</button>
                        </form>
                        <div onClick={toggleEditForm} style={{ cursor: 'pointer' }} className="close-box"><i className="la la-close"></i></div>
                    </div>
                </div>
                <div className="overlay-background-edit"></div>
            </div>
        )
    }
}

const mapStateToProps = state => {
    return{
        account: state.account
    }
}
export default connect(mapStateToProps,null)(FormUpdateBasicInformation)